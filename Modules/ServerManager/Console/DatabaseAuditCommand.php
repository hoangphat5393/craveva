<?php

namespace Modules\ServerManager\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Finder\Finder;

class DatabaseAuditCommand extends Command
{
    protected $signature = 'servermanager:db-audit {--format=json : Output format: json|text}';

    protected $description = 'Audit database tables: list all, detect unused by static code references';

    public function handle()
    {
        $this->info('Starting database audit...');

        $connection = DB::connection();
        $schemaManager = $connection->getDoctrineSchemaManager();
        $tables = $schemaManager->listTableNames();

        $referenced = $this->scanCodeReferences();

        $unused = array_values(array_diff($tables, $referenced));

        $summary = [
            'total_tables' => count($tables),
            'tables' => $tables,
            'referenced_tables' => array_values(array_unique($referenced)),
            'unused_candidates' => $unused,
        ];

        $format = $this->option('format');
        if ($format === 'json') {
            $this->line(json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->line('Total tables: '.$summary['total_tables']);
            $this->line('Unused candidates ('.count($unused).'):');
            foreach ($unused as $t) {
                $this->line('- '.$t);
            }
        }

        $this->info('Database audit completed.');

        return Command::SUCCESS;
    }

    private function scanCodeReferences(): array
    {
        $finder = new Finder;
        $finder->files()
            ->in(base_path('app'))
            ->in(base_path('Modules'))
            ->in(resource_path('views'))
            ->name('*.php')
            ->name('*.blade.php');

        $referenced = [];

        $patterns = [
            '/DB::table\(\s*[\'\"]([a-zA-Z0-9_]+)[\'\"]\s*\)/',
            '/->table\(\s*[\'\"]([a-zA-Z0-9_]+)[\'\"]\s*\)/',
            '/->from\(\s*[\'\"]([a-zA-Z0-9_]+)[\'\"]\s*\)/',
            '/->join\(\s*[\'\"]([a-zA-Z0-9_]+)[\'\"]\s*[,)]/',
            '/->leftJoin\(\s*[\'\"]([a-zA-Z0-9_]+)[\'\"]\s*[,)]/',
            '/->rightJoin\(\s*[\'\"]([a-zA-Z0-9_]+)[\'\"]\s*[,)]/',
            '/->crossJoin\(\s*[\'\"]([a-zA-Z0-9_]+)[\'\"]\s*[,)]/',
            '/Schema::table\(\s*[\'\"]([a-zA-Z0-9_]+)[\'\"]\s*\)/',
            '/protected\s+\$table\s*=\s*[\'\"]([a-zA-Z0-9_]+)[\'\"]\s*;/',
            '/belongsToMany\([^,]+,\s*[\'\"]([a-zA-Z0-9_]+)[\'\"]/',
        ];

        foreach ($finder as $file) {
            $contents = $file->getContents();
            foreach ($patterns as $pattern) {
                if (preg_match_all($pattern, $contents, $matches)) {
                    foreach ($matches[1] as $table) {
                        $referenced[] = $table;
                    }
                }
            }
        }

        return array_values(array_unique($referenced));
    }
}
