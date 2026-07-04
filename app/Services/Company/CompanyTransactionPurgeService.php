<?php

namespace App\Services\Company;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class CompanyTransactionPurgeService
{
    /**
     * @return list<array{phase: string, table: string, scope: string, count: int, skipped: bool, reason: ?string}>
     */
    public function dryRun(int $companyId, bool $includeBoms = false): array
    {
        return $this->run($companyId, execute: false, includeBoms: $includeBoms);
    }

    /**
     * @return list<array{phase: string, table: string, scope: string, count: int, skipped: bool, reason: ?string}>
     */
    public function execute(int $companyId, bool $includeBoms = false): array
    {
        return DB::transaction(
            fn (): array => $this->run($companyId, execute: true, includeBoms: $includeBoms)
        );
    }

    /**
     * @return list<array{phase: string, table: string, scope: string, count: int, skipped: bool, reason: ?string}>
     */
    private function run(int $companyId, bool $execute, bool $includeBoms): array
    {
        $rows = [];

        foreach (CompanyTransactionPurgePlan::steps($includeBoms) as $step) {
            if (! Schema::hasTable($step->table)) {
                $rows[] = $this->resultRow($step, 0, true, 'table_missing');

                continue;
            }

            $query = $this->buildQuery($step, $companyId);

            if ($query === null) {
                $rows[] = $this->resultRow($step, 0, true, 'invalid_step');

                continue;
            }

            $count = (int) $query->count();

            if ($execute && $count > 0) {
                $query->delete();
            }

            $rows[] = $this->resultRow($step, $count, false, null);
        }

        return $rows;
    }

    private function buildQuery(CompanyTransactionPurgeStep $step, int $companyId): ?Builder
    {
        $table = $step->table;

        if ($step->scope === 'company') {
            if (! Schema::hasColumn($table, 'company_id')) {
                return null;
            }

            return DB::table($table)->where('company_id', $companyId);
        }

        if ($step->scope === 'child_of_company') {
            $childColumn = $step->childColumn;
            $parentTable = $step->parentTable;
            $parentLinkColumn = $step->parentLinkColumn ?? 'id';

            if ($childColumn === null || $parentTable === null) {
                return null;
            }

            if (! Schema::hasTable($parentTable) || ! Schema::hasColumn($parentTable, 'company_id')) {
                return null;
            }

            if (! Schema::hasColumn($table, $childColumn)) {
                return null;
            }

            return DB::table($table)->whereIn($childColumn, function ($sub) use ($parentTable, $parentLinkColumn, $companyId): void {
                $sub->select($parentLinkColumn)
                    ->from($parentTable)
                    ->where('company_id', $companyId);
            });
        }

        return null;
    }

    /**
     * @return array{phase: string, table: string, scope: string, count: int, skipped: bool, reason: ?string}
     */
    private function resultRow(CompanyTransactionPurgeStep $step, int $count, bool $skipped, ?string $reason): array
    {
        return [
            'phase' => $step->phase,
            'table' => $step->table,
            'scope' => $step->scope,
            'count' => $count,
            'skipped' => $skipped,
            'reason' => $reason,
        ];
    }
}
