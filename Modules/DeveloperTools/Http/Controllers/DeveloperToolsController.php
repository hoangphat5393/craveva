<?php

namespace Modules\DeveloperTools\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\DeveloperTools\Entities\DbAccessLog;
use Modules\DeveloperTools\Entities\DbUserMapping;
use Modules\DeveloperTools\Entities\DeveloperToolsCredential;
use Modules\DeveloperTools\Entities\FileRecord;
use Modules\DeveloperTools\Services\DbAccessPolicy;
use Modules\DeveloperTools\Services\FileScanner;

class DeveloperToolsController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Developer Tools';
        $this->activeSettingMenu = 'developertools';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $company = company();
        if (! $company) {
            abort(403, 'Company context required');
        }

        $this->credentials = DeveloperToolsCredential::where('company_id', $company->id)->get();
        $policy = app(DbAccessPolicy::class);
        $this->availableModules = $policy->availableModules();
        $this->defaultModules = $policy->defaultModules();
        $this->accessLogs = DbAccessLog::where('company_id', $company->id)->latest()->limit(25)->get();

        return view('developertools::index', $this->data);
    }

    public function codeMap(Request $request)
    {
        if (! user()->is_superadmin) {
            abort(403, 'Only Super Admin can access CodeMap');
        }

        $this->pageTitle = 'CodeMap';
        $this->activeSettingMenu = 'codemap';

        try {
            $query = FileRecord::query();

            if ($request->filled('q')) {
                $q = $request->get('q');
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%$q%")
                        ->orWhere('path', 'like', "%$q%")
                        ->orWhere('role', 'like', "%$q%");
                });
            }

            if ($request->filled('language')) {
                $query->where('language', $request->get('language'));
            }

            if ($request->filled('module')) {
                $query->where('module', $request->get('module'));
            }

            $this->records = $query->orderBy('path')->paginate(30);
        } catch (\Throwable $e) {
            $this->records = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 30);
            session()->flash('error', 'Bảng dữ liệu CodeMap chưa migrate. Vui lòng chạy php artisan migrate.');
        }

        return view('developertools::codemap.index', $this->data);
    }

    public function exportCodeMap(Request $request)
    {
        if (! user()->is_superadmin) {
            abort(403, 'Only Super Admin can access CodeMap');
        }

        try {
            $query = FileRecord::query();

            if ($request->filled('q')) {
                $q = $request->get('q');
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%$q%")
                        ->orWhere('path', 'like', "%$q%")
                        ->orWhere('role', 'like', "%$q%");
                });
            }

            if ($request->filled('language')) {
                $query->where('language', $request->get('language'));
            }

            if ($request->filled('module')) {
                $query->where('module', $request->get('module'));
            }

            $records = $query->orderBy('path')->limit(2000)->get();

            return response()->json([
                'data' => $records,
                'count' => $records->count(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'data' => [],
                'count' => 0,
                'error' => 'CodeMap chưa migrate.',
            ]);
        }
    }

    public function scanCodeMap(Request $request)
    {
        if (! user()->is_superadmin) {
            abort(403);
        }

        (new FileScanner)->scanAndStore();

        return back()->with('success', 'Đã quét và lưu thông tin file thành công.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Log::info('DeveloperTools store method called');
        $company = company();
        if (! $company) {
            Log::error('No company context found');
            abort(403, 'Company context required');
        }

        $policy = app(DbAccessPolicy::class);
        $requestedModules = $request->input('modules', []);
        if (! is_array($requestedModules)) {
            $requestedModules = [];
        }
        $requestedModules = array_values(array_filter($requestedModules, fn($m) => is_string($m) && $m !== ''));
        $effectiveModules = $policy->normalizeRequestedModules($requestedModules);

        // 1. Generate Creds

        // Use a safe prefix and random string
        $randomSuffix = strtolower(Str::random(8));
        $dbUsername = 'api_' . $company->id . '_' . $randomSuffix;
        // Limit username length to 32 chars for MySQL compatibility (older versions had 16, newer 32)
        $dbUsername = substr($dbUsername, 0, 32);

        $dbPassword = Str::random(20);
        // NEW: Dynamic Database Name per Company for Virtual Data Layer
        $gatewayDb = 'api_gateway_' . $company->id;

        // Sanitize DB name just in case
        $gatewayDbSafe = preg_replace('/[^a-zA-Z0-9_]/', '', $gatewayDb);

        $startedAt = microtime(true);
        $warnings = [];
        $createdViewsCount = 0;

        try {
            Log::info('Attempting to create DB user: ' . $dbUsername);

            // 1. Create Database if not exists
            DB::statement("CREATE DATABASE IF NOT EXISTS `$gatewayDbSafe` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            Log::info('Created/Verified database: ' . $gatewayDbSafe);

            // 2. Create Views for Multi-tenancy (Virtual Data Layer)
            // Resolve actual source database name reliably
            $mainDb = DB::connection()->getDatabaseName();
            $schemaExists = DB::table('information_schema.SCHEMATA')
                ->where('SCHEMA_NAME', $mainDb)
                ->exists();

            if (! $schemaExists) {
                $row = DB::selectOne('SELECT DATABASE() AS db');
                if ($row && isset($row->db) && is_string($row->db) && $row->db !== '') {
                    $mainDb = $row->db;
                }
            }

            // Use quoted identifier for source DB to handle names with dots (e.g. craveva.test)
            $mainDbQuoted = '`' . str_replace('`', '``', $mainDb) . '`';

            $mainDbSafe = $policy->sanitizeIdentifier($mainDb);
            if ($mainDbSafe === '') {
                // Fallback if sanitization fails completely
                $mainDbSafe = preg_replace('/[^a-zA-Z0-9]/', '', $mainDb);
            }

            $allowedTables = $policy->resolveAllowedTables($mainDb, $effectiveModules);
            $globalTables = $policy->globalTables();
            $joinViews = $policy->joinViews();

            $tablesWithCompanyId = [];
            foreach (array_chunk($allowedTables, 200) as $chunk) {
                $found = DB::table('information_schema.COLUMNS')
                    ->where('TABLE_SCHEMA', $mainDb)
                    ->where('COLUMN_NAME', 'company_id')
                    ->whereIn('TABLE_NAME', $chunk)
                    ->pluck('TABLE_NAME')
                    ->toArray();

                foreach ($found as $t) {
                    $tablesWithCompanyId[$t] = true;
                }
            }

            foreach ($allowedTables as $tableName) {
                $tableNameSafe = $policy->sanitizeIdentifier($tableName);
                if ($tableNameSafe !== $tableName || $tableNameSafe === '') {
                    $warnings[] = "Skipped invalid table name: {$tableName}";

                    continue;
                }

                // Source table name quoted (handle special chars if any)
                $tableNameQuoted = '`' . str_replace('`', '``', $tableName) . '`';

                DB::statement("DROP VIEW IF EXISTS `$gatewayDbSafe`.`$tableNameSafe`");

                if (array_key_exists($tableName, $joinViews)) {
                    $def = $joinViews[$tableName];
                    // Use quoted DB name. Remove backticks from replacement if config assumes none.
                    $from = str_replace('{mainDb}', $mainDbQuoted, $def['from']);
                    $where = str_replace('{companyId}', (string) ((int) $company->id), $def['where']);
                    $select = $def['select'];

                    DB::statement("
                        CREATE VIEW `$gatewayDbSafe`.`$tableNameSafe` AS
                        SELECT {$select}
                        FROM {$from}
                        WHERE {$where}
                    ");
                    $createdViewsCount++;

                    continue;
                }

                $selectColumns = $policy->selectColumnsForTable($mainDb, $tableName);

                if (in_array($tableName, $globalTables, true)) {
                    DB::statement("
                        CREATE VIEW `$gatewayDbSafe`.`$tableNameSafe` AS
                        SELECT {$selectColumns} FROM {$mainDbQuoted}.{$tableNameQuoted}
                    ");
                    $createdViewsCount++;

                    continue;
                }

                if (isset($tablesWithCompanyId[$tableName])) {
                    DB::statement("
                        CREATE VIEW `$gatewayDbSafe`.`$tableNameSafe` AS
                        SELECT {$selectColumns} FROM {$mainDbQuoted}.{$tableNameQuoted}
                        WHERE `company_id` = {$company->id}
                    ");
                    $createdViewsCount++;

                    continue;
                }

                $warnings[] = "Skipped unscoped table (no company_id / no join rule): {$tableName}";
            }

            Log::info('Created Views for company: ' . $company->id);

            // 3. Create MySQL User (Requires privileges)
            // Note: This runs as the application's DB user.
            // If it fails, the user needs to grant CREATE USER privileges to the app user.
            // DDL statements cause implicit commit, so we cannot use DB::beginTransaction() here.
            $pdo = DB::getPdo();
            $dbUsernameSafe = $policy->sanitizeIdentifier($dbUsername);
            if ($dbUsernameSafe === '') {
                throw new \RuntimeException('Invalid generated DB username');
            }
            $dbUsername = $dbUsernameSafe;
            $userQuoted = $pdo->quote($dbUsername);
            $passQuoted = $pdo->quote($dbPassword);

            DB::statement("CREATE USER {$userQuoted}@'%' IDENTIFIED BY {$passQuoted}");
            Log::info('Created user');

            // Grant permissions on the Gateway DB ONLY
            // We cannot bind parameters for GRANT statement in some drivers, so we carefully construct it.
            // Since $dbUsername and $gatewayDb are generated/config controlled, it's relatively safe.

            DB::statement("GRANT SELECT ON `$gatewayDbSafe`.* TO {$userQuoted}@'%'");
            DB::statement('FLUSH PRIVILEGES');
            Log::info('Granted privileges');

            // 3. Map User to Company
            DbUserMapping::create([
                'db_username' => $dbUsername,
                'company_id' => $company->id,
            ]);

            // 4. Store Credentials for Display
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $credential = DeveloperToolsCredential::create([
                'company_id' => $company->id,
                'db_username' => $dbUsername,
                'db_database' => $gatewayDb,
                'db_host' => request()->getHost(), // Simple guess
                'allowed_modules' => $effectiveModules,
                'created_views_count' => $createdViewsCount,
                'generation_duration_ms' => $durationMs,
                'last_generated_at' => now(),
                'last_generation_warnings' => empty($warnings) ? null : implode("\n", $warnings),
                'created_by' => user() ? user()->id : null,
            ]);

            Log::info('Credential records created');

            DbAccessLog::create([
                'company_id' => $company->id,
                'db_username' => $dbUsername,
                'db_database' => $gatewayDb,
                'requested_modules' => $requestedModules,
                'allowed_tables_count' => count($allowedTables),
                'created_views_count' => $createdViewsCount,
                'duration_ms' => $durationMs,
                'status' => 'success',
                'warnings' => empty($warnings) ? null : implode("\n", $warnings),
                'created_by' => user() ? user()->id : null,
            ]);

            // Flash password to session (only show once)
            session()->flash('new_db_password', $dbPassword);
            session()->flash('new_db_username', $dbUsername);
            session()->flash('new_db_name', $gatewayDb);
            session()->flash('new_db_modules', $effectiveModules);
            session()->flash('new_db_views_count', $createdViewsCount);

            return back()->with('success', 'Database credential created successfully. Please save the password now.');
        } catch (\Exception $e) {
            Log::error('Failed to create credential: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Clean up if user was created but DB transaction failed?
            // DB transaction doesn't cover CREATE USER usually.
            // We attempt to drop user if it exists.

            try {
                DB::statement("DROP USER IF EXISTS '$dbUsername'@'%'");
            } catch (\Exception $dropEx) {
            }

            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            try {
                DbAccessLog::create([
                    'company_id' => $company->id,
                    'db_username' => $dbUsername ?? null,
                    'db_database' => $gatewayDb ?? null,
                    'requested_modules' => $requestedModules ?? null,
                    'allowed_tables_count' => null,
                    'created_views_count' => $createdViewsCount ?? null,
                    'duration_ms' => $durationMs,
                    'status' => 'failed',
                    'warnings' => empty($warnings) ? null : implode("\n", $warnings),
                    'error_message' => $e->getMessage(),
                    'created_by' => user() ? user()->id : null,
                ]);
            } catch (\Throwable $logEx) {
            }

            return back()->with('error', 'Failed to create database user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $company = company();
        if (! $company) {
            abort(403);
        }

        $credential = DeveloperToolsCredential::where('company_id', $company->id)->findOrFail($id);

        try {
            DB::statement("DROP USER IF EXISTS '{$credential->db_username}'@'%'");
        } catch (\Exception $e) {
            // User might already be deleted or permission denied
            // Continue to delete record
        }

        // Delete mapping
        DbUserMapping::where('db_username', $credential->db_username)->delete();

        // Delete credential record
        $credential->delete();

        return back()->with('success', 'Credential revoked.');
    }
}
