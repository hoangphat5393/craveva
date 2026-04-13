<?php

namespace Modules\DeveloperTools\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DbAccessPolicy
{
    public function availableModules(): array
    {
        return config('developertools.db_access.modules', []);
    }

    /**
     * Modules shown as checkboxes on Developer Tools (excludes internal-only e.g. custom fields always merged on credential create).
     *
     * @return array<string, array<string, mixed>>
     */
    public function availableModulesForUi(): array
    {
        return array_filter(
            $this->availableModules(),
            static fn(array $def): bool => empty($def['internal_only'])
        );
    }

    public function defaultModules(): array
    {
        return config('developertools.db_access.default_modules', []);
    }

    public function normalizeRequestedModules(array $requested): array
    {
        $available = array_keys($this->availableModules());
        $requested = array_values(array_unique(array_filter($requested, fn($m) => is_string($m) && $m !== '')));

        $requested = array_values(array_intersect($requested, $available));

        if (empty($requested)) {
            $requested = $this->defaultModules();
            $requested = array_values(array_intersect($requested, $available));
        }

        $expanded = [];
        foreach ($requested as $moduleKey) {
            $this->expandModuleWithDependencies($moduleKey, $expanded);
        }

        return array_values(array_unique($expanded));
    }

    public function resolveAllowedTables(string $mainDb, array $requestedModules): array
    {
        $requestedModules = $this->normalizeRequestedModules($requestedModules);
        $modules = $this->availableModules();
        $denyTables = $this->denyTables();
        $tables = [];

        $schemaTables = DB::table('information_schema.TABLES')
            ->where('TABLE_SCHEMA', $mainDb)
            ->where('TABLE_TYPE', 'BASE TABLE')
            ->pluck('TABLE_NAME')
            ->toArray();

        foreach ($requestedModules as $moduleKey) {
            $patterns = Arr::get($modules, "{$moduleKey}.table_patterns", []);
            $tables = array_merge($tables, $this->matchTablesByPatterns($schemaTables, $patterns));
        }

        $tables = array_values(array_unique(array_filter($tables, fn($t) => is_string($t) && $t !== '')));
        $tables = array_values(array_diff($tables, $denyTables));

        $sensitive = $this->sensitiveTables();
        $tables = array_values(array_filter($tables, function ($table) use ($sensitive) {
            $rule = $sensitive[$table] ?? null;

            return ! is_array($rule) || empty($rule['deny']);
        }));

        sort($tables);

        return $tables;
    }

    public function globalTables(): array
    {
        return config('developertools.db_access.global_tables', []);
    }

    public function denyTables(): array
    {
        return config('developertools.db_access.deny_tables', []);
    }

    public function sensitiveTables(): array
    {
        return config('developertools.db_access.sensitive_tables', []);
    }

    public function joinViews(): array
    {
        return config('developertools.db_access.join_views', []);
    }

    public function sanitizeIdentifier(string $identifier): string
    {
        $identifier = preg_replace('/[^a-zA-Z0-9]+/', '_', $identifier) ?? '';
        $identifier = preg_replace('/_+/', '_', $identifier) ?? '';
        $identifier = trim($identifier, '_');

        return $identifier;
    }

    public function selectColumnsForTable(string $mainDb, string $tableName): string
    {
        $rules = $this->sensitiveTables();
        $allowColumns = Arr::get($rules, "{$tableName}.allow_columns");

        if (! is_array($allowColumns) || empty($allowColumns)) {
            return '*';
        }

        $existing = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', $mainDb)
            ->where('TABLE_NAME', $tableName)
            ->pluck('COLUMN_NAME')
            ->toArray();

        $existingMap = array_fill_keys($existing, true);
        $safe = [];

        foreach ($allowColumns as $col) {
            if (! is_string($col) || $col === '') {
                continue;
            }
            if (isset($existingMap[$col])) {
                $safe[] = '`' . str_replace('`', '``', $col) . '`';
            }
        }

        if (empty($safe)) {
            return '*';
        }

        return implode(', ', $safe);
    }

    public function matchTablesByPatterns(array $schemaTables, array $patterns): array
    {
        $patterns = array_values(array_filter($patterns, fn($p) => is_string($p) && $p !== ''));
        if (empty($patterns)) {
            return [];
        }

        $regexes = array_map([$this, 'patternToRegex'], $patterns);

        $matched = [];
        foreach ($schemaTables as $table) {
            foreach ($regexes as $re) {
                if (preg_match($re, $table)) {
                    $matched[] = $table;
                    break;
                }
            }
        }

        return $matched;
    }

    private function expandModuleWithDependencies(string $moduleKey, array &$expanded): void
    {
        $modules = $this->availableModules();
        if (! array_key_exists($moduleKey, $modules)) {
            return;
        }

        if (! in_array($moduleKey, $expanded, true)) {
            $expanded[] = $moduleKey;
        }

        $deps = Arr::get($modules, "{$moduleKey}.depends_on", []);
        if (! is_array($deps)) {
            return;
        }

        foreach ($deps as $dep) {
            if (is_string($dep) && $dep !== '') {
                $this->expandModuleWithDependencies($dep, $expanded);
            }
        }
    }

    private function patternToRegex(string $pattern): string
    {
        $quoted = preg_quote($pattern, '/');
        $quoted = str_replace('%', '.*', $quoted);

        return '/^' . $quoted . '$/i';
    }
}
