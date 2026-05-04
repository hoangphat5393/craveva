<?php

declare(strict_types=1);

final class AiContextGenerator
{
    private string $root;

    private string $out;

    private string $generatedAt;

    private array $ignorePathContains = [
        DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR.'node_modules'.DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR.'LanguagePack'.DIRECTORY_SEPARATOR.'Languages'.DIRECTORY_SEPARATOR,
    ];

    public function __construct(string $root)
    {
        $this->root = rtrim($root, DIRECTORY_SEPARATOR);
        $this->out = $this->root.DIRECTORY_SEPARATOR.'ai-context';
        $this->generatedAt = gmdate('c');
    }

    public function run(): void
    {
        $this->ensureDirs([
            $this->out,
            $this->out.'/core',
            $this->out.'/modules',
            $this->out.'/workflows',
            $this->out.'/audit',
            $this->out.'/modernization',
            $this->out.'/rag',
            $this->out.'/architecture',
        ]);

        $modules = $this->discoverModules();
        $core = $this->scanCore($modules);
        $moduleDocs = [];

        foreach ($modules as $m) {
            $moduleDocs[] = $this->scanModule($m);
        }

        $this->writeCore($core, $moduleDocs);
        $this->writeWorkflows($core, $moduleDocs);
        $this->writeArchitecture($core, $moduleDocs);
        $this->writeAudit($core, $moduleDocs);
        $this->writeModernization($core, $moduleDocs);
        $this->writeRag($core, $moduleDocs);

        foreach ($moduleDocs as $doc) {
            $this->writeModule($doc);
        }

        $this->writeText($this->out.'/README.txt', implode("\n", [
            'AI context generated.',
            'Generated at (UTC): '.$this->generatedAt,
            'Root: '.$this->root,
            'Modules: '.count($modules),
            '',
            'Entry points:',
            '- '.$this->out.'/core/SYSTEM_OVERVIEW.md',
            '- '.$this->out.'/core/MODULE_INDEX.md',
        ])."\n");
    }

    private function discoverModules(): array
    {
        $paths = glob($this->root.DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'module.json') ?: [];
        sort($paths);

        $modules = [];
        foreach ($paths as $p) {
            $name = basename(dirname($p));
            $modules[] = [
                'name' => $name,
                'path' => dirname($p),
                'module_json' => $p,
            ];
        }

        return $modules;
    }

    private function scanCore(array $modules): array
    {
        $composer = $this->readJson($this->root.'/composer.json');
        $pkg = $this->readJson($this->root.'/package.json');

        $laravel = $composer['require']['laravel/framework'] ?? null;
        $php = $composer['require']['php'] ?? null;
        $modulesPkg = $composer['require']['nwidart/laravel-modules'] ?? null;

        $routes = [
            $this->root.'/routes/web.php',
            $this->root.'/routes/web-settings.php',
            $this->root.'/routes/web-public.php',
            $this->root.'/routes/api.php',
            $this->root.'/routes/channels.php',
            $this->root.'/routes/SuperAdmin/web.php',
            $this->root.'/routes/SuperAdmin/web-public.php',
        ];

        $routeSummaries = [];
        foreach ($routes as $r) {
            if (! is_file($r)) {
                continue;
            }
            $routeSummaries[] = [
                'file' => $this->rel($r),
                'routes' => $this->extractRoutes($r),
            ];
        }

        $scripts = [];
        if (isset($composer['scripts']) && is_array($composer['scripts'])) {
            $scripts['composer'] = array_keys($composer['scripts']);
        }
        if (isset($pkg['scripts']) && is_array($pkg['scripts'])) {
            $scripts['npm'] = array_keys($pkg['scripts']);
        }

        $scanTargets = [
            'app/Http/Controllers' => $this->root.'/app/Http/Controllers',
            'app/Models' => $this->root.'/app/Models',
            'database/migrations' => $this->root.'/database/migrations',
            'resources/views' => $this->root.'/resources/views',
            'resources/js' => $this->root.'/resources/js',
            'public/js' => $this->root.'/public/js',
            'public/css' => $this->root.'/public/css',
        ];

        $coreFileStats = [];
        foreach ($scanTargets as $k => $dir) {
            $coreFileStats[$k] = $this->countFiles($dir);
        }

        $risks = $this->scanRisks([
            $this->root.'/app',
            $this->root.'/Modules',
            $this->root.'/resources',
            $this->root.'/routes',
        ]);

        return [
            'generated_at' => $this->generatedAt,
            'stack' => [
                'php' => $php,
                'laravel' => $laravel,
                'nwidart/laravel-modules' => $modulesPkg,
            ],
            'scripts' => $scripts,
            'modules' => $modules,
            'routes' => $routeSummaries,
            'core_file_stats' => $coreFileStats,
            'risk_signals' => $risks,
        ];
    }

    private function scanModule(array $m): array
    {
        $base = $m['path'];
        $moduleJson = $this->readJson($m['module_json']);
        $files = $this->listModuleFiles($base);

        $routes = [];
        foreach ($files['routes'] as $f) {
            $routes[] = [
                'file' => $this->rel($f),
                'routes' => $this->extractRoutes($f),
            ];
        }

        $controllers = $this->summarizePhpFiles($files['controllers']);
        $entities = $this->summarizePhpFiles($files['entities']);
        $services = $this->summarizePhpFiles($files['services']);
        $repositories = $this->summarizePhpFiles($files['repositories']);
        $jobs = $this->summarizePhpFiles($files['jobs']);
        $events = $this->summarizePhpFiles($files['events']);
        $listeners = $this->summarizePhpFiles($files['listeners']);
        $policies = $this->summarizePhpFiles($files['policies']);

        $ui = [
            'views_count' => count($files['views']),
            'views_examples' => array_slice(array_map([$this, 'rel'], $files['views']), 0, 40),
            'js_count' => count($files['js']),
            'css_count' => count($files['css']),
        ];

        $statusSignals = $this->extractStatusSignals(array_merge(
            $files['controllers'],
            $files['services'],
            $files['repositories'],
            $files['entities'],
            $files['jobs']
        ));

        $db = $this->extractDbStructure($files['migrations'], $files['entities']);
        $deps = $this->extractDependencies($base);

        $fatControllers = array_values(array_filter($controllers, function (array $c) {
            return ($c['line_count'] ?? 0) >= 350 || ($c['method_count'] ?? 0) >= 18;
        }));

        $logicInController = $this->detectLogicInControllers($files['controllers']);

        return [
            'name' => $m['name'],
            'path' => $this->rel($base),
            'module_json' => $moduleJson,
            'routes' => $routes,
            'controllers' => $controllers,
            'services' => $services,
            'repositories' => $repositories,
            'entities' => $entities,
            'migrations' => array_map([$this, 'rel'], $files['migrations']),
            'events' => $events,
            'listeners' => $listeners,
            'jobs' => $jobs,
            'policies' => $policies,
            'ui' => $ui,
            'db' => $db,
            'status' => $statusSignals,
            'dependencies' => $deps,
            'findings' => [
                'fat_controllers' => $fatControllers,
                'business_logic_in_controllers' => $logicInController,
            ],
        ];
    }

    private function listModuleFiles(string $base): array
    {
        $targets = [
            'routes' => [$base.'/Routes'],
            'controllers' => [$base.'/Http/Controllers'],
            'entities' => [$base.'/Entities'],
            'services' => [$base.'/Services', $base.'/Service'],
            'repositories' => [$base.'/Repositories', $base.'/Repository'],
            'events' => [$base.'/Events'],
            'listeners' => [$base.'/Listeners'],
            'jobs' => [$base.'/Jobs'],
            'policies' => [$base.'/Policies'],
            'migrations' => [$base.'/Database/Migrations'],
            'views' => [$base.'/Resources/views'],
            'js' => [$base.'/Resources/assets/js', $base.'/Resources/js'],
            'css' => [$base.'/Resources/assets/css', $base.'/Resources/css'],
        ];

        $out = [];
        foreach ($targets as $key => $dirs) {
            $out[$key] = [];
            foreach ($dirs as $d) {
                if (! is_dir($d)) {
                    continue;
                }
                $out[$key] = array_merge($out[$key], $this->findFiles($d, $key === 'views' ? '/\.blade\.php$/' : ($key === 'migrations' ? '/\.php$/' : '/\.php$/')));
            }
            sort($out[$key]);
        }

        return $out;
    }

    private function findFiles(string $dir, string $pattern): array
    {
        $files = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            $p = $f->getPathname();
            if ($this->isIgnored($p)) {
                continue;
            }
            if (! preg_match($pattern, $p)) {
                continue;
            }
            $files[] = $p;
        }

        return $files;
    }

    private function isIgnored(string $path): bool
    {
        $n = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        foreach ($this->ignorePathContains as $needle) {
            if (str_contains($n, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function summarizePhpFiles(array $files): array
    {
        $out = [];
        foreach ($files as $f) {
            $content = @file_get_contents($f);
            if ($content === false) {
                continue;
            }
            $lineCount = substr_count($content, "\n") + 1;
            preg_match_all('/public\s+function\s+([a-zA-Z0-9_]+)\s*\(/', $content, $m);
            $methods = array_values(array_unique($m[1] ?? []));

            $out[] = [
                'file' => $this->rel($f),
                'line_count' => $lineCount,
                'method_count' => count($methods),
                'public_methods' => array_slice($methods, 0, 60),
            ];
        }

        return $out;
    }

    private function extractRoutes(string $file): array
    {
        $c = @file_get_contents($file);
        if ($c === false) {
            return [];
        }

        $routes = [];

        preg_match_all('/Route::(get|post|put|patch|delete)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,/i', $c, $m1, PREG_SET_ORDER);
        foreach ($m1 as $m) {
            $routes[] = [
                'type' => strtolower($m[1]),
                'uri' => $m[2],
            ];
        }

        preg_match_all('/Route::(resource|apiResource)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,/i', $c, $m2, PREG_SET_ORDER);
        foreach ($m2 as $m) {
            $routes[] = [
                'type' => strtolower($m[1]),
                'uri' => $m[2],
            ];
        }

        $routes = array_values(array_unique($routes, SORT_REGULAR));
        usort($routes, fn ($a, $b) => strcmp($a['uri'], $b['uri']));

        return array_slice($routes, 0, 250);
    }

    private function extractStatusSignals(array $phpFiles): array
    {
        $literals = [];
        $assignments = 0;
        foreach ($phpFiles as $f) {
            $c = @file_get_contents($f);
            if ($c === false) {
                continue;
            }
            preg_match_all('/->status\s*=\s*[\'"]([^\'"]+)[\'"]/', $c, $m1);
            foreach (($m1[1] ?? []) as $v) {
                $literals[$v] = true;
            }
            preg_match_all('/\bstatus\b\s*=>\s*[\'"]([^\'"]+)[\'"]/', $c, $m2);
            foreach (($m2[1] ?? []) as $v) {
                $literals[$v] = true;
            }
            $assignments += preg_match_all('/->status\s*=/', $c);
        }

        $values = array_keys($literals);
        sort($values);

        return [
            'status_literals' => array_slice($values, 0, 80),
            'status_assignment_hits' => $assignments,
        ];
    }

    private function extractDbStructure(array $migrationFiles, array $entityFiles): array
    {
        $tables = [];
        foreach ($migrationFiles as $f) {
            $c = @file_get_contents($f);
            if ($c === false) {
                continue;
            }
            if (! preg_match_all('/Schema::create\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,/i', $c, $m)) {
                continue;
            }
            foreach ($m[1] as $table) {
                $tables[$table] = $tables[$table] ?? ['table' => $table, 'columns' => [], 'migrations' => []];
                $tables[$table]['migrations'][] = basename($f);
            }
            preg_match_all('/->(?:string|text|longText|integer|bigInteger|unsignedBigInteger|unsignedInteger|boolean|tinyInteger|smallInteger|decimal|double|float|date|dateTime|timestamp|json|enum)\s*\(\s*[\'"]([^\'"]+)[\'"]/', $c, $cols);
            foreach (($cols[1] ?? []) as $col) {
                foreach ($m[1] as $table) {
                    $tables[$table]['columns'][$col] = true;
                }
            }
        }

        $entities = [];
        foreach ($entityFiles as $f) {
            $c = @file_get_contents($f);
            if ($c === false) {
                continue;
            }
            $table = null;
            if (preg_match('/protected\s+\$table\s*=\s*[\'"]([^\'"]+)[\'"]\s*;/', $c, $m)) {
                $table = $m[1];
            }
            $casts = [];
            if (preg_match('/protected\s+\$casts\s*=\s*\[(.*?)\];/s', $c, $m)) {
                preg_match_all('/[\'"]([^\'"]+)[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"]/', $m[1], $mm, PREG_SET_ORDER);
                foreach ($mm as $row) {
                    $casts[$row[1]] = $row[2];
                }
            }
            $entities[] = [
                'file' => $this->rel($f),
                'table' => $table,
                'casts' => array_slice($casts, 0, 40, true),
            ];
        }

        $tableList = array_values($tables);
        foreach ($tableList as &$t) {
            $t['columns'] = array_slice(array_values(array_map(fn ($k) => $k, array_keys($t['columns']))), 0, 80);
            sort($t['columns']);
            $t['migrations'] = array_slice(array_values(array_unique($t['migrations'])), 0, 20);
        }
        unset($t);
        usort($tableList, fn ($a, $b) => strcmp($a['table'], $b['table']));

        return [
            'tables' => array_slice($tableList, 0, 120),
            'entities' => array_slice($entities, 0, 120),
        ];
    }

    private function extractDependencies(string $moduleBase): array
    {
        $deps = [];
        $phpFiles = $this->findFiles($moduleBase, '/\.php$/');
        foreach ($phpFiles as $f) {
            $c = @file_get_contents($f);
            if ($c === false) {
                continue;
            }
            preg_match_all('/\bModules\\\\([A-Za-z0-9_]+)\\\\/m', $c, $m);
            foreach (($m[1] ?? []) as $d) {
                if ($d === basename($moduleBase)) {
                    continue;
                }
                $deps[$d] = true;
            }
            preg_match_all('/\b([a-z0-9_]+)::/i', $c, $m2);
            foreach (($m2[1] ?? []) as $ns) {
                if (strlen($ns) < 3 || strlen($ns) > 24) {
                    continue;
                }
                if (! preg_match('/^[a-z]/', $ns)) {
                    continue;
                }
                if (in_array($ns, ['route', 'view', 'config', 'auth', 'cache', 'app', 'request', 'session', 'response', 'schema', 'db', 'log', 'file', 'storage'], true)) {
                    continue;
                }
                $deps['view_ns:'.strtolower($ns)] = true;
            }
        }

        $out = array_keys($deps);
        sort($out);

        return array_slice($out, 0, 120);
    }

    private function detectLogicInControllers(array $controllerFiles): array
    {
        $signals = [];
        foreach ($controllerFiles as $f) {
            $c = @file_get_contents($f);
            if ($c === false) {
                continue;
            }
            $hits = [];
            foreach ([
                'DB::transaction' => '/DB::transaction\s*\(/',
                'DB::table' => '/DB::table\s*\(/',
                'Model save' => '/->save\s*\(/',
                'Model create' => '/::create\s*\(/',
                'Query join' => '/->join\s*\(/',
                'Raw SQL' => '/(selectRaw|whereRaw|orderByRaw|DB::statement)\s*\(/',
                'File upload' => '/(move\(|store\(|storeAs\(|putFile\()/',
            ] as $label => $re) {
                if (preg_match($re, $c)) {
                    $hits[] = $label;
                }
            }
            if ($hits) {
                $signals[] = [
                    'file' => $this->rel($f),
                    'signals' => $hits,
                ];
            }
        }

        return array_slice($signals, 0, 80);
    }

    private function scanRisks(array $dirs): array
    {
        $patterns = [
            'raw_sql' => '/(DB::statement|unprepared|selectRaw|whereRaw|orderByRaw)\s*\(/',
            'mass_assignment' => '/->fill\s*\(|::create\s*\(/',
            'file_ops' => '/(unlink\(|file_put_contents\(|fopen\(|chmod\(|chown\()/',
            'command_exec' => '/(exec\(|shell_exec\(|passthru\(|proc_open\()/',
            'deserialize' => '/\bunserialize\s*\(/',
            'eval' => '/\beval\s*\(/',
            'crypto_decrypt' => '/\bdecrypt\s*\(/',
            'api_keys' => '/(api_key|secret_key|client_secret)/i',
        ];

        $hits = [];
        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
            foreach ($it as $f) {
                $p = $f->getPathname();
                if ($this->isIgnored($p)) {
                    continue;
                }
                if (! preg_match('/\.(php|blade\.php|js)$/', $p)) {
                    continue;
                }
                $c = @file_get_contents($p);
                if ($c === false) {
                    continue;
                }
                foreach ($patterns as $k => $re) {
                    if (preg_match($re, $c)) {
                        $hits[$k][] = $this->rel($p);
                    }
                }
            }
        }

        foreach ($hits as $k => $list) {
            $list = array_values(array_unique($list));
            sort($list);
            $hits[$k] = array_slice($list, 0, 80);
        }

        return $hits;
    }

    private function countFiles(string $dir): array
    {
        if (! is_dir($dir)) {
            return ['count' => 0];
        }
        $count = 0;
        $countPhp = 0;
        $countBlade = 0;
        $countJs = 0;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            $p = $f->getPathname();
            if ($this->isIgnored($p)) {
                continue;
            }
            $count++;
            if (preg_match('/\.php$/', $p)) {
                $countPhp++;
            }
            if (preg_match('/\.blade\.php$/', $p)) {
                $countBlade++;
            }
            if (preg_match('/\.js$/', $p)) {
                $countJs++;
            }
        }

        return [
            'count' => $count,
            'php' => $countPhp,
            'blade' => $countBlade,
            'js' => $countJs,
        ];
    }

    private function writeCore(array $core, array $moduleDocs): void
    {
        $moduleIndexLines = [
            '# MODULE_INDEX',
            '',
            '- Generated at: '.$core['generated_at'],
            '- Modules: '.count($moduleDocs),
            '',
        ];
        foreach ($moduleDocs as $m) {
            $moduleIndexLines[] = '- '.$m['name'].': modules/'.$m['name'].'/overview.md';
        }
        $moduleIndexLines[] = '';

        $overview = [];
        $overview[] = '# SYSTEM_OVERVIEW';
        $overview[] = '';
        $overview[] = '- Generated at: '.$core['generated_at'];
        $overview[] = '- Stack: PHP '.($core['stack']['php'] ?? 'unknown').', Laravel '.($core['stack']['laravel'] ?? 'unknown').', nwidart/laravel-modules '.($core['stack']['nwidart/laravel-modules'] ?? 'unknown');
        $overview[] = '- Modules discovered: '.count($moduleDocs);
        $overview[] = '';
        $overview[] = '## Entry routes';
        $overview[] = '';
        foreach ($core['routes'] as $r) {
            $overview[] = '- '.$r['file'].' ('.count($r['routes']).' routes extracted)';
        }
        $overview[] = '';
        $overview[] = '## Scan coverage (counts)';
        $overview[] = '';
        foreach ($core['core_file_stats'] as $k => $stat) {
            $overview[] = '- '.$k.': total='.($stat['count'] ?? 0).', php='.($stat['php'] ?? 0).', blade='.($stat['blade'] ?? 0).', js='.($stat['js'] ?? 0);
        }
        $overview[] = '';
        $overview[] = '## Risk signals (heuristics)';
        $overview[] = '';
        foreach (($core['risk_signals'] ?? []) as $k => $list) {
            $overview[] = '- '.$k.': '.count($list).' files';
        }
        $overview[] = '';
        $overview[] = '## References';
        $overview[] = '';
        $overview[] = '- MASTER_DOCUMENTATION.md';
        $overview[] = '- FUNC_BUG/ (bug notes)';
        $overview[] = '- FUNC_LOGIC/ (flow notes)';
        $overview[] = '';

        $philosophy = implode("\n", [
            '# ERP_PHILOSOPHY',
            '',
            '- Module-first architecture: domain logic should live inside Modules/*; keep cross-module coupling explicit.',
            '- Workflow consistency: sales → delivery → invoice → payment should be traceable and idempotent.',
            '- Inventory consistency: stock movements should be canonical, reversible, and auditable.',
            '- Maintainability: reduce fat controllers; push business logic into services/domain classes.',
            '- Scalability: remove N+1 patterns; enforce eager loading; use queues for heavy tasks.',
            '',
        ]);

        $this->writeText($this->out.'/core/MODULE_INDEX.md', implode("\n", $moduleIndexLines));
        $this->writeText($this->out.'/core/SYSTEM_OVERVIEW.md', implode("\n", $overview));
        $this->writeText($this->out.'/core/ERP_PHILOSOPHY.md', $philosophy);
    }

    private function writeModule(array $m): void
    {
        $dir = $this->out.'/modules/'.$m['name'];
        $this->ensureDirs([$dir]);

        $overview = [];
        $overview[] = '# '.$m['name'].' — Overview';
        $overview[] = '';
        $overview[] = '- Generated at: '.$this->generatedAt;
        $overview[] = '- Module path: '.$m['path'];
        $overview[] = '';
        $overview[] = '## Module metadata';
        $overview[] = '';
        $overview[] = '```json';
        $overview[] = json_encode($m['module_json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $overview[] = '```';
        $overview[] = '';
        $overview[] = '## Quick stats';
        $overview[] = '';
        $overview[] = '- Routes files: '.count($m['routes']);
        $overview[] = '- Controllers: '.count($m['controllers']);
        $overview[] = '- Entities: '.count($m['entities']);
        $overview[] = '- Services: '.count($m['services']);
        $overview[] = '- Repositories: '.count($m['repositories']);
        $overview[] = '- Migrations: '.count($m['migrations']);
        $overview[] = '- Views: '.($m['ui']['views_count'] ?? 0);
        $overview[] = '';

        $this->writeText($dir.'/overview.md', implode("\n", $overview)."\n");

        $this->writeText($dir.'/routes.md', $this->asMarkdownList('Routes', $m['routes']));
        $this->writeText($dir.'/controllers.md', $this->asMarkdownList('Controllers', $m['controllers']));
        $this->writeText($dir.'/services.md', $this->asMarkdownList('Services', $m['services']));
        $this->writeText($dir.'/db_structure.md', $this->asMarkdownDb($m['db']));
        $this->writeText($dir.'/events.md', $this->asMarkdownEvents($m));
        $this->writeText($dir.'/permissions.md', $this->asMarkdownPermissions($m));
        $this->writeText($dir.'/ui_notes.md', $this->asMarkdownUi($m));
        $this->writeText($dir.'/workflow.md', $this->asMarkdownWorkflow($m));
        $this->writeText($dir.'/business_rules.md', $this->asMarkdownBusinessRules($m));
        $this->writeText($dir.'/status_flow.md', $this->asMarkdownStatus($m));
    }

    private function writeWorkflows(array $core, array $moduleDocs): void
    {
        $this->writeText($this->out.'/workflows/sales_to_delivery.md', $this->workflowTemplate('sales_to_delivery', $moduleDocs, ['Order', 'Invoice', 'Payment', 'Delivery', 'Warehouse', 'Purchase']));
        $this->writeText($this->out.'/workflows/inventory_transaction.md', $this->workflowTemplate('inventory_transaction', $moduleDocs, ['Warehouse', 'Purchase', 'Product', 'Stock', 'Inventory']));
        $this->writeText($this->out.'/workflows/payment_flow.md', $this->workflowTemplate('payment_flow', $moduleDocs, ['Payment', 'Invoice', 'Order', 'Bank']));
    }

    private function writeAudit(array $core, array $moduleDocs): void
    {
        $security = [];
        $security[] = '# SECURITY_REPORT';
        $security[] = '';
        $security[] = '- Generated at: '.$core['generated_at'];
        $security[] = '';
        $security[] = '## Risk signals (heuristics)';
        $security[] = '';
        foreach (($core['risk_signals'] ?? []) as $k => $list) {
            $security[] = '### '.$k;
            $security[] = '';
            foreach (array_slice($list, 0, 40) as $f) {
                $security[] = '- '.$f;
            }
            $security[] = '';
        }

        $techDebt = [];
        $techDebt[] = '# TECH_DEBT_REPORT';
        $techDebt[] = '';
        $techDebt[] = '- Generated at: '.$core['generated_at'];
        $techDebt[] = '';
        $techDebt[] = '## Fat controllers (heuristics)';
        $techDebt[] = '';
        foreach ($moduleDocs as $m) {
            foreach (($m['findings']['fat_controllers'] ?? []) as $c) {
                $techDebt[] = '- '.$m['name'].': '.$c['file'].' (lines='.$c['line_count'].', methods='.$c['method_count'].')';
            }
        }
        $techDebt[] = '';
        $techDebt[] = '## Business logic inside controllers (signals)';
        $techDebt[] = '';
        foreach ($moduleDocs as $m) {
            foreach (array_slice(($m['findings']['business_logic_in_controllers'] ?? []), 0, 20) as $c) {
                $techDebt[] = '- '.$m['name'].': '.$c['file'].' → '.implode(', ', $c['signals']);
            }
        }
        $techDebt[] = '';

        $highRisk = [];
        $highRisk[] = '# HIGH_RISK_WORKFLOW';
        $highRisk[] = '';
        $highRisk[] = '- Generated at: '.$core['generated_at'];
        $highRisk[] = '';
        $highRisk[] = '## Inventory / finance consistency candidates';
        $highRisk[] = '';
        $highRisk[] = '- Scan modules with keywords: warehouse, stock, inventory, purchase, invoice, payment.';
        $highRisk[] = '- Review status transitions and idempotency of stock movements and postings.';
        $highRisk[] = '';

        $this->writeText($this->out.'/audit/SECURITY_REPORT.md', implode("\n", $security)."\n");
        $this->writeText($this->out.'/audit/TECH_DEBT_REPORT.md', implode("\n", $techDebt)."\n");
        $this->writeText($this->out.'/audit/HIGH_RISK_WORKFLOW.md', implode("\n", $highRisk)."\n");
    }

    private function writeArchitecture(array $core, array $moduleDocs): void
    {
        $map = [];
        $map[] = '# MODULE_DEPENDENCY_MAP';
        $map[] = '';
        $map[] = '- Generated at: '.$core['generated_at'];
        $map[] = '';

        foreach ($moduleDocs as $m) {
            $deps = array_filter($m['dependencies'], fn ($d) => ! str_starts_with($d, 'view_ns:'));
            $map[] = '## '.$m['name'];
            $map[] = '';
            if (! $deps) {
                $map[] = '- Dependencies: (none detected)';
            } else {
                $map[] = '- Dependencies: '.implode(', ', array_slice($deps, 0, 30));
            }
            $map[] = '';
        }

        $svc = implode("\n", [
            '# SERVICE_ARCHITECTURE',
            '',
            '- Generated at: '.$core['generated_at'],
            '',
            '## Notes',
            '',
            '- This repo mixes controller-driven logic with services in some modules. Prioritize service extraction for inventory/finance workflows.',
            '- Standardize: Request validation → domain service → repository/unit-of-work → events.',
            '',
        ]);

        $eventFlow = implode("\n", [
            '# EVENT_FLOW',
            '',
            '- Generated at: '.$core['generated_at'],
            '',
            '## Notes',
            '',
            '- This file is a starter map. For accurate event flow, scan for event(new ...), dispatch(), and listener registrations in each module/provider.',
            '',
        ]);

        $this->writeText($this->out.'/architecture/MODULE_DEPENDENCY_MAP.md', implode("\n", $map)."\n");
        $this->writeText($this->out.'/architecture/SERVICE_ARCHITECTURE.md', $svc);
        $this->writeText($this->out.'/architecture/EVENT_FLOW.md', $eventFlow);
    }

    private function writeModernization(array $core, array $moduleDocs): void
    {
        $roadmap = [];
        $roadmap[] = '# MODERNIZATION_ROADMAP';
        $roadmap[] = '';
        $roadmap[] = '- Generated at: '.$core['generated_at'];
        $roadmap[] = '';
        $roadmap[] = '## Phase 0 (no behavior change)';
        $roadmap[] = '';
        $roadmap[] = '- Generate and maintain /ai-context (this folder).';
        $roadmap[] = '- Add lightweight static checks (Larastan, Pint) to CI if not present.';
        $roadmap[] = '- Define module boundaries and allowed cross-module dependencies.';
        $roadmap[] = '';
        $roadmap[] = '## Phase 1 (safety & correctness)';
        $roadmap[] = '';
        $roadmap[] = '- Inventory: centralize stock movement posting + idempotency keys.';
        $roadmap[] = '- Finance: standardize invoice/payment posting and reconciliation.';
        $roadmap[] = '- Harden secrets encryption flows (APP_KEY mismatch recovery playbook).';
        $roadmap[] = '';
        $roadmap[] = '## Phase 2 (maintainability)';
        $roadmap[] = '';
        $roadmap[] = '- Extract services from fat controllers for high-risk flows.';
        $roadmap[] = '- Introduce status enums/state machines for major workflows.';
        $roadmap[] = '- Replace jQuery ajax patterns with a unified API client where appropriate.';
        $roadmap[] = '';

        $priority = [];
        $priority[] = '# REFACTOR_PRIORITY';
        $priority[] = '';
        $priority[] = '- Generated at: '.$core['generated_at'];
        $priority[] = '';
        $priority[] = '## Top candidates (heuristics)';
        $priority[] = '';
        foreach ($moduleDocs as $m) {
            foreach (array_slice(($m['findings']['fat_controllers'] ?? []), 0, 5) as $c) {
                $priority[] = '- '.$m['name'].': '.$c['file'].' (lines='.$c['line_count'].', methods='.$c['method_count'].')';
            }
        }
        $priority[] = '';

        $this->writeText($this->out.'/modernization/MODERNIZATION_ROADMAP.md', implode("\n", $roadmap)."\n");
        $this->writeText($this->out.'/modernization/REFACTOR_PRIORITY.md', implode("\n", $priority)."\n");
    }

    private function writeRag(array $core, array $moduleDocs): void
    {
        $modules = [];
        foreach ($moduleDocs as $m) {
            $modules[] = [
                'name' => $m['name'],
                'path' => $m['path'],
                'dependencies' => $m['dependencies'],
                'routes' => array_slice(array_merge(...array_map(fn ($r) => $r['routes'], $m['routes'])), 0, 120),
                'status_literals' => $m['status']['status_literals'] ?? [],
                'ui' => $m['ui'],
            ];
        }

        $features = [];
        foreach ($moduleDocs as $m) {
            $uris = [];
            foreach ($m['routes'] as $r) {
                foreach ($r['routes'] as $rr) {
                    $uris[$rr['uri']] = true;
                }
            }
            $features[] = [
                'module' => $m['name'],
                'features' => array_slice(array_keys($uris), 0, 80),
            ];
        }

        $workflows = [
            [
                'id' => 'sales_to_delivery',
                'title' => 'Sales → Delivery → Invoice → Payment',
                'related_modules' => $this->matchModulesByKeywords($moduleDocs, ['order', 'invoice', 'payment', 'warehouse', 'purchase', 'delivery']),
            ],
            [
                'id' => 'inventory_transaction',
                'title' => 'Inventory Transaction (Inbound/Outbound/Transfer)',
                'related_modules' => $this->matchModulesByKeywords($moduleDocs, ['warehouse', 'stock', 'inventory', 'purchase', 'grn']),
            ],
            [
                'id' => 'payment_flow',
                'title' => 'Payment Flow',
                'related_modules' => $this->matchModulesByKeywords($moduleDocs, ['payment', 'invoice', 'bank']),
            ],
        ];

        $faq = [
            [
                'q' => 'What causes "The MAC is invalid." on settings pages?',
                'a' => 'Encrypted cast attributes cannot be decrypted when APP_KEY differs from the key used to encrypt the stored payload (often after DB restore). Do not prefill secrets, re-enter secrets, or align APP_KEY.',
                'ref' => 'FUNC_BUG/SOCIAL_AUTH_SETTINGS_MAC_INVALID_FIX.md',
            ],
        ];

        $this->writeJson($this->out.'/rag/modules.json', ['generated_at' => $core['generated_at'], 'modules' => $modules]);
        $this->writeJson($this->out.'/rag/features.json', ['generated_at' => $core['generated_at'], 'features' => $features]);
        $this->writeJson($this->out.'/rag/workflow.json', ['generated_at' => $core['generated_at'], 'workflows' => $workflows]);
        $this->writeJson($this->out.'/rag/faq.json', ['generated_at' => $core['generated_at'], 'faq' => $faq]);
    }

    private function matchModulesByKeywords(array $moduleDocs, array $keywords): array
    {
        $out = [];
        foreach ($moduleDocs as $m) {
            $hay = strtolower($m['name'].' '.json_encode($m['module_json'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            foreach ($keywords as $kw) {
                if (str_contains($hay, strtolower($kw))) {
                    $out[] = $m['name'];
                    break;
                }
            }
        }
        sort($out);

        return array_values(array_unique($out));
    }

    private function workflowTemplate(string $id, array $moduleDocs, array $keywords): string
    {
        $related = $this->matchModulesByKeywords($moduleDocs, $keywords);

        return implode("\n", [
            '# '.$id,
            '',
            '- Generated at: '.$this->generatedAt,
            '- Related modules (heuristic): '.($related ? implode(', ', $related) : '(none detected)'),
            '',
            '## Steps (draft)',
            '',
            '- Identify entry routes and controllers.',
            '- Identify DB writes and status transitions.',
            '- Identify inventory/finance postings and idempotency guards.',
            '- Identify approvals and reversals/cancellations.',
            '',
        ]);
    }

    private function asMarkdownList(string $title, array $data): string
    {
        $lines = ['# '.$title, '', '- Generated at: '.$this->generatedAt, ''];
        if (! $data) {
            $lines[] = '- (none)';
            $lines[] = '';

            return implode("\n", $lines);
        }

        foreach (array_slice($data, 0, 200) as $row) {
            if (isset($row['file'])) {
                $lines[] = '- '.$row['file'].' (lines='.($row['line_count'] ?? '?').', methods='.($row['method_count'] ?? '?').')';

                continue;
            }
            if (isset($row['file']) && isset($row['routes'])) {
                $lines[] = '- '.$row['file'].' ('.count($row['routes']).' routes)';

                continue;
            }
        }
        $lines[] = '';

        if (isset($data[0]['routes'])) {
            $lines[] = '## Route samples';
            $lines[] = '';
            foreach ($data as $r) {
                $lines[] = '### '.$r['file'];
                $lines[] = '';
                foreach (array_slice($r['routes'], 0, 60) as $rt) {
                    $lines[] = '- '.$rt['type'].' '.$rt['uri'];
                }
                $lines[] = '';
            }
        }

        return implode("\n", $lines);
    }

    private function asMarkdownDb(array $db): string
    {
        $lines = ['# DB_STRUCTURE', '', '- Generated at: '.$this->generatedAt, ''];
        $tables = $db['tables'] ?? [];
        if ($tables) {
            $lines[] = '## Tables (from module migrations)';
            $lines[] = '';
            foreach (array_slice($tables, 0, 80) as $t) {
                $lines[] = '### '.$t['table'];
                $lines[] = '';
                if (! empty($t['columns'])) {
                    $lines[] = '- Columns: '.implode(', ', array_slice($t['columns'], 0, 40));
                }
                if (! empty($t['migrations'])) {
                    $lines[] = '- Migrations: '.implode(', ', $t['migrations']);
                }
                $lines[] = '';
            }
        }

        $entities = $db['entities'] ?? [];
        if ($entities) {
            $lines[] = '## Entities (table + casts)';
            $lines[] = '';
            foreach (array_slice($entities, 0, 60) as $e) {
                $lines[] = '- '.$e['file'].($e['table'] ? (' (table='.$e['table'].')') : '');
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private function asMarkdownEvents(array $m): string
    {
        $lines = ['# EVENTS', '', '- Generated at: '.$this->generatedAt, ''];
        $lines[] = '## Events';
        $lines[] = '';
        foreach (array_slice(($m['events'] ?? []), 0, 80) as $e) {
            $lines[] = '- '.$e['file'];
        }
        $lines[] = '';
        $lines[] = '## Listeners';
        $lines[] = '';
        foreach (array_slice(($m['listeners'] ?? []), 0, 80) as $e) {
            $lines[] = '- '.$e['file'];
        }
        $lines[] = '';
        $lines[] = '## Jobs';
        $lines[] = '';
        foreach (array_slice(($m['jobs'] ?? []), 0, 80) as $e) {
            $lines[] = '- '.$e['file'];
        }
        $lines[] = '';

        return implode("\n", $lines);
    }

    private function asMarkdownPermissions(array $m): string
    {
        $lines = ['# PERMISSIONS', '', '- Generated at: '.$this->generatedAt, ''];
        $lines[] = '- This generator does not infer exact permission names yet. Map permissions from: app/Models/Module.php and DB seeds.';
        $lines[] = '- Module: '.$m['name'];
        $lines[] = '';

        return implode("\n", $lines);
    }

    private function asMarkdownUi(array $m): string
    {
        $lines = ['# UI_NOTES', '', '- Generated at: '.$this->generatedAt, ''];
        $lines[] = '- Views count: '.($m['ui']['views_count'] ?? 0);
        $lines[] = '';
        foreach (($m['ui']['views_examples'] ?? []) as $v) {
            $lines[] = '- '.$v;
        }
        $lines[] = '';

        return implode("\n", $lines);
    }

    private function asMarkdownWorkflow(array $m): string
    {
        $lines = ['# WORKFLOW', '', '- Generated at: '.$this->generatedAt, ''];
        $lines[] = '## Entry routes';
        $lines[] = '';
        foreach (array_slice(($m['routes'] ?? []), 0, 10) as $r) {
            $lines[] = '- '.$r['file'];
        }
        $lines[] = '';
        $lines[] = '## Notes';
        $lines[] = '';
        $lines[] = '- Identify CRUD vs workflow endpoints (status changes, approvals, postings).';
        $lines[] = '- Identify inventory/finance impacts (stock movement, ledger posting).';
        $lines[] = '';

        return implode("\n", $lines);
    }

    private function asMarkdownBusinessRules(array $m): string
    {
        $lines = ['# BUSINESS_RULES', '', '- Generated at: '.$this->generatedAt, ''];
        $lines[] = '## Controller signals';
        $lines[] = '';
        foreach (array_slice(($m['findings']['business_logic_in_controllers'] ?? []), 0, 40) as $c) {
            $lines[] = '- '.$c['file'].': '.implode(', ', $c['signals']);
        }
        $lines[] = '';

        return implode("\n", $lines);
    }

    private function asMarkdownStatus(array $m): string
    {
        $lines = ['# STATUS_FLOW', '', '- Generated at: '.$this->generatedAt, ''];
        $lines[] = '- Status assignment hits: '.($m['status']['status_assignment_hits'] ?? 0);
        $lines[] = '';
        $lines[] = '## Status literals (heuristic)';
        $lines[] = '';
        foreach (array_slice(($m['status']['status_literals'] ?? []), 0, 80) as $s) {
            $lines[] = '- '.$s;
        }
        $lines[] = '';

        return implode("\n", $lines);
    }

    private function writeText(string $path, string $content): void
    {
        $dir = dirname($path);
        $this->ensureDirs([$dir]);
        file_put_contents($path, $content);
    }

    private function writeJson(string $path, array $data): void
    {
        $this->writeText($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");
    }

    private function ensureDirs(array $dirs): void
    {
        foreach ($dirs as $d) {
            if ($d === '' || $d === '.' || $d === '/') {
                continue;
            }
            if (! is_dir($d)) {
                mkdir($d, 0775, true);
            }
        }
    }

    private function readJson(string $file): array
    {
        if (! is_file($file)) {
            return [];
        }
        $c = file_get_contents($file);
        if ($c === false) {
            return [];
        }
        $d = json_decode($c, true);

        return is_array($d) ? $d : [];
    }

    private function rel(string $path): string
    {
        $p = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
        $r = $this->root.DIRECTORY_SEPARATOR;
        if (str_starts_with($p, $r)) {
            return str_replace(DIRECTORY_SEPARATOR, '/', substr($p, strlen($r)));
        }

        return str_replace(DIRECTORY_SEPARATOR, '/', $p);
    }
}

$root = dirname(__DIR__);
$gen = new AiContextGenerator($root);
$gen->run();
