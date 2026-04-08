<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditCustomFieldsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'custom-fields:audit';

    /**
     * @var string
     */
    protected $description = 'Audit custom field definitions and custom_fields_data for company isolation issues';

    /**
     * Entity rows whose company_id must match the custom field group\'s company_id when both are set.
     *
     * @var list<array{model: string, table: string, companyColumn: string}>
     */
    private const ENTITY_CHECKS = [
        ['model' => 'App\\Models\\Product', 'table' => 'products', 'companyColumn' => 'company_id'],
        ['model' => 'App\\Models\\ClientDetails', 'table' => 'client_details', 'companyColumn' => 'company_id'],
        ['model' => 'App\\Models\\Invoice', 'table' => 'invoices', 'companyColumn' => 'company_id'],
        ['model' => 'App\\Models\\Task', 'table' => 'tasks', 'companyColumn' => 'company_id'],
        ['model' => 'App\\Models\\Lead', 'table' => 'leads', 'companyColumn' => 'company_id'],
        ['model' => 'App\\Models\\Project', 'table' => 'projects', 'companyColumn' => 'company_id'],
        ['model' => 'App\\Models\\Expense', 'table' => 'expenses', 'companyColumn' => 'company_id'],
        ['model' => 'App\\Models\\Order', 'table' => 'orders', 'companyColumn' => 'company_id'],
    ];

    public function handle(): int
    {
        if (! Schema::hasTable('custom_fields') || ! Schema::hasTable('custom_field_groups') || ! Schema::hasTable('custom_fields_data')) {
            $this->warn('Custom field tables are missing; skipping audit.');

            return self::SUCCESS;
        }

        $totalIssues = 0;

        $fieldVsGroup = DB::table('custom_fields as cf')
            ->join('custom_field_groups as cfg', 'cf.custom_field_group_id', '=', 'cfg.id')
            ->whereNotNull('cf.company_id')
            ->whereNotNull('cfg.company_id')
            ->whereColumn('cf.company_id', '!=', 'cfg.company_id')
            ->count();
        $this->line('custom_fields.company_id != custom_field_groups.company_id: '.$fieldVsGroup);
        $totalIssues += $fieldVsGroup;

        foreach (self::ENTITY_CHECKS as $check) {
            if (! Schema::hasTable($check['table'])) {
                continue;
            }

            $model = $check['model'];
            $table = $check['table'];
            $col = $check['companyColumn'];
            // Avoid backslashes in console output: Symfony OutputFormatter treats \ as escape.
            $modelLabel = str_replace('\\', '/', $model);

            $count = DB::table('custom_fields_data as cfd')
                ->join('custom_fields as cf', 'cfd.custom_field_id', '=', 'cf.id')
                ->join('custom_field_groups as cfg', 'cf.custom_field_group_id', '=', 'cfg.id')
                ->join($table.' as ent', function ($join) use ($model) {
                    $join->on('cfd.model_id', '=', 'ent.id')
                        ->where('cfd.model', '=', $model);
                })
                ->whereNotNull('cfg.company_id')
                ->whereNotNull('ent.'.$col)
                ->whereColumn('cfg.company_id', '!=', 'ent.'.$col)
                ->count();

            if ($count > 0) {
                $this->warn("custom_fields_data vs {$table}.{$col} mismatch ({$modelLabel}): {$count}");
            } else {
                $this->line("custom_fields_data vs {$table}.{$col} ({$modelLabel}): 0");
            }
            $totalIssues += $count;
        }

        if ($totalIssues === 0) {
            $this->info('No company isolation issues found for the checks above.');

            return self::SUCCESS;
        }

        $this->error('Found '.$totalIssues.' potential cross-company custom field row(s). Review DB or re-save entities after fixing definitions.');

        return self::FAILURE;
    }
}
