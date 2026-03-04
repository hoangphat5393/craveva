<?php

namespace Modules\DeveloperTools\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DeveloperTools\Entities\DeveloperToolsCredential;
use Modules\DeveloperTools\Entities\DbUserMapping;
use Modules\DeveloperTools\Entities\FileRecord;
use Modules\DeveloperTools\Services\FileScanner;
use Illuminate\Support\Facades\Log;

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
        if (!$company) {
            abort(403, 'Company context required');
        }

        $this->credentials = DeveloperToolsCredential::where('company_id', $company->id)->get();
        return view('developertools::index', $this->data);
    }

    public function codeMap(Request $request)
    {
        if (!user()->is_superadmin) {
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
        if (!user()->is_superadmin) {
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
        if (!user()->is_superadmin) {
            abort(403);
        }

        (new FileScanner())->scanAndStore();
        return back()->with('success', 'Đã quét và lưu thông tin file thành công.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Log::info('DeveloperTools store method called');
        $company = company();
        if (!$company) {
            Log::error('No company context found');
            abort(403, 'Company context required');
        }

        // 1. Generate Creds

        // Use a safe prefix and random string
        $randomSuffix = strtolower(Str::random(8));
        $dbUsername = 'api_' . $company->id . '_' . $randomSuffix;
        // Limit username length to 32 chars for MySQL compatibility (older versions had 16, newer 32)
        $dbUsername = substr($dbUsername, 0, 32);

        $dbPassword = Str::random(20);
        $gatewayDb = config('developertools.gateway_db', 'api_gateway_db');

        try {
            Log::info('Attempting to create DB user: ' . $dbUsername);

            // 2. Create MySQL User (Requires privileges)
            // Note: This runs as the application's DB user.
            // If it fails, the user needs to grant CREATE USER privileges to the app user.
            // DDL statements cause implicit commit, so we cannot use DB::beginTransaction() here.
            DB::statement("CREATE USER ?@'%' IDENTIFIED BY ?", [$dbUsername, $dbPassword]);
            Log::info('Created user');

            // Grant permissions on the Gateway DB ONLY
            // We cannot bind parameters for GRANT statement in some drivers, so we carefully construct it.
            // Since $dbUsername and $gatewayDb are generated/config controlled, it's relatively safe.

            // Sanitize DB name just in case
            $gatewayDbSafe = preg_replace('/[^a-zA-Z0-9_]/', '', $gatewayDb);

            DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON `$gatewayDbSafe`.* TO '$dbUsername'@'%'");
            DB::statement("FLUSH PRIVILEGES");
            Log::info('Granted privileges');

            // 3. Map User to Company
            DbUserMapping::create([
                'db_username' => $dbUsername,
                'company_id' => $company->id,
            ]);

            // 4. Store Credentials for Display
            DeveloperToolsCredential::create([
                'company_id' => $company->id,
                'db_username' => $dbUsername,
                'db_database' => $gatewayDb,
                'db_host' => request()->getHost(), // Simple guess
                'created_by' => user() ? user()->id : null,
            ]);

            Log::info('Credential records created');

            // Flash password to session (only show once)
            session()->flash('new_db_password', $dbPassword);
            session()->flash('new_db_username', $dbUsername);

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

            return back()->with('error', 'Failed to create database user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $company = company();
        if (!$company) {
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
