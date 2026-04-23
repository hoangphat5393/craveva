<?php

namespace Modules\Warehouse\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WarehouseBatchDedupeCommand extends Command
{
    protected $signature = 'warehouse:batch-dedupe
        {--apply : Apply merge + delete duplicate rows}
        {--company_id= : Optional company scope}';

    protected $description = 'Detect and deduplicate duplicate warehouse batch identity rows';

    public function handle(): int
    {
        $companyId = $this->option('company_id') !== null ? (int) $this->option('company_id') : null;
        $apply = (bool) $this->option('apply');

        $groups = $this->duplicateGroups($companyId);
        $duplicateGroupCount = $groups->count();

        if ($duplicateGroupCount === 0) {
            $this->info('No duplicate warehouse batch identity groups found.');

            return self::SUCCESS;
        }

        $this->warn('Duplicate groups found: '.$duplicateGroupCount);

        $previewRows = 0;
        foreach ($groups as $group) {
            $rows = $this->groupRows($group);
            $previewRows += max(0, $rows->count() - 1);
        }

        $this->line('Duplicate rows (excluding canonical rows): '.$previewRows);

        if (! $apply) {
            $this->line('Dry-run mode only. Re-run with --apply to execute dedupe.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($groups): void {
            foreach ($groups as $group) {
                $rows = $this->groupRows($group);
                if ($rows->count() <= 1) {
                    continue;
                }

                $canonical = $rows->first();
                $duplicateIds = $rows->skip(1)->pluck('id')->map(fn ($id) => (int) $id)->all();
                if ($duplicateIds === []) {
                    continue;
                }

                $totalQuantity = (float) $rows->sum('quantity');
                $totalReserved = (float) $rows->sum('reserved_quantity');
                $manufacturingDate = $rows->pluck('manufacturing_date')->filter()->first() ?: null;

                DB::table('warehouse_product_batches')
                    ->where('id', $canonical->id)
                    ->update([
                        'quantity' => $totalQuantity,
                        'reserved_quantity' => $totalReserved,
                        'manufacturing_date' => $manufacturingDate,
                        'updated_at' => now(),
                    ]);

                DB::table('sales_do_items')
                    ->whereIn('warehouse_batch_id', $duplicateIds)
                    ->update(['warehouse_batch_id' => $canonical->id]);

                DB::table('warehouse_product_batches')
                    ->whereIn('id', $duplicateIds)
                    ->delete();
            }
        });

        $this->info('Dedupe completed successfully.');

        return self::SUCCESS;
    }

    private function duplicateGroups(?int $companyId)
    {
        return DB::table('warehouse_product_batches')
            ->selectRaw('company_id, warehouse_id, product_id, batch_number, expiration_date, COUNT(*) as duplicate_count')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->groupBy('company_id', 'warehouse_id', 'product_id', 'batch_number', 'expiration_date')
            ->havingRaw('COUNT(*) > 1')
            ->get();
    }

    private function groupRows(object $group)
    {
        return DB::table('warehouse_product_batches')
            ->where('company_id', $group->company_id)
            ->where('warehouse_id', $group->warehouse_id)
            ->where('product_id', $group->product_id)
            ->when(
                $group->batch_number === null,
                fn ($q) => $q->whereNull('batch_number'),
                fn ($q) => $q->where('batch_number', $group->batch_number)
            )
            ->when(
                $group->expiration_date === null,
                fn ($q) => $q->whereNull('expiration_date'),
                fn ($q) => $q->whereDate('expiration_date', $group->expiration_date)
            )
            ->orderBy('id')
            ->get();
    }
}
