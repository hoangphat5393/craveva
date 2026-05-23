<?php

declare(strict_types=1);

namespace Modules\Warehouse\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Entities\Warehouse;

/**
 * @phpstan-type EnsureDefaultWarehouseResult array{
 *     company_id: int,
 *     company_name: string,
 *     action: string,
 *     warehouse_id: int|null,
 *     warehouse_name: string|null,
 *     note: string,
 * }
 */
class EnsureDefaultWarehouseService
{
    public function warehousesTableExists(): bool
    {
        return Schema::hasTable('warehouses');
    }

    /**
     * @return EnsureDefaultWarehouseResult
     */
    public function ensureForCompany(int $companyId, string $companyName, bool $dryRun = false): array
    {
        if (! $this->warehousesTableExists()) {
            return $this->result($companyId, $companyName, 'skipped', null, null, 'warehouses table missing');
        }

        $activeQuery = Warehouse::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('status', 'active');

        $activeCount = (clone $activeQuery)->count();

        if ($activeCount === 0) {
            return $this->createDefaultWarehouse($companyId, $companyName, $dryRun);
        }

        $defaults = Warehouse::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->where('is_default', true)
            ->orderBy('id')
            ->get();

        if ($defaults->count() === 1) {
            $warehouse = $defaults->first();

            return $this->result(
                $companyId,
                $companyName,
                'already_ok',
                (int) $warehouse->id,
                (string) $warehouse->name,
                'Default warehouse already set'
            );
        }

        if ($defaults->count() > 1) {
            $keeper = $this->orderedActiveWarehouses($companyId)->first();

            if ($keeper === null) {
                return $this->createDefaultWarehouse($companyId, $companyName, $dryRun);
            }

            if (! $dryRun) {
                $this->setSingleDefault($companyId, (int) $keeper->id);
            }

            return $this->result(
                $companyId,
                $companyName,
                'set_default',
                (int) $keeper->id,
                (string) $keeper->name,
                $dryRun ? 'Would normalize multiple defaults to one' : 'Normalized multiple defaults to one warehouse'
            );
        }

        $first = $this->orderedActiveWarehouses($companyId)->first();

        if ($first === null) {
            return $this->createDefaultWarehouse($companyId, $companyName, $dryRun);
        }

        if (! $dryRun) {
            $this->setSingleDefault($companyId, (int) $first->id);
        }

        return $this->result(
            $companyId,
            $companyName,
            'set_default',
            (int) $first->id,
            (string) $first->name,
            $dryRun ? 'Would set first active warehouse as default' : 'Set first active warehouse as default'
        );
    }

    public function resolveDefaultWarehouseId(int $companyId): ?int
    {
        if (! $this->warehousesTableExists()) {
            return null;
        }

        $id = Warehouse::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->where('is_default', true)
            ->orderBy('id')
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    /**
     * @return Collection<int, Warehouse>
     */
    protected function orderedActiveWarehouses(int $companyId)
    {
        $query = Warehouse::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('status', 'active');

        if (Schema::hasColumn('warehouses', 'sort_order')) {
            $query->orderBy('sort_order')->orderBy('name')->orderBy('id');
        } else {
            $query->orderBy('name')->orderBy('id');
        }

        return $query->get();
    }

    protected function setSingleDefault(int $companyId, int $warehouseId): void
    {
        Warehouse::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('id', '!=', $warehouseId)
            ->update(['is_default' => false]);

        Warehouse::withoutGlobalScopes()
            ->where('id', $warehouseId)
            ->update(['is_default' => true]);
    }

    /**
     * @return EnsureDefaultWarehouseResult
     */
    protected function createDefaultWarehouse(int $companyId, string $companyName, bool $dryRun): array
    {
        $code = $this->uniqueDefaultCode($companyId);

        if ($dryRun) {
            return $this->result(
                $companyId,
                $companyName,
                'created',
                null,
                'Default Warehouse',
                "Would create default warehouse (code {$code})"
            );
        }

        $sortOrder = 0;
        if (Schema::hasColumn('warehouses', 'sort_order')) {
            $sortOrder = (int) (Warehouse::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->max('sort_order') ?? -1) + 1;
        }

        Warehouse::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->update(['is_default' => false]);

        $warehouse = Warehouse::create([
            'company_id' => $companyId,
            'name' => 'Default Warehouse',
            'code' => $code,
            'warehouse_type' => 'normal',
            'status' => 'active',
            'is_default' => true,
            'sort_order' => $sortOrder,
        ]);

        return $this->result(
            $companyId,
            $companyName,
            'created',
            (int) $warehouse->id,
            (string) $warehouse->name,
            'Created default warehouse'
        );
    }

    protected function uniqueDefaultCode(int $companyId): string
    {
        $base = 'DFWH';
        $exists = Warehouse::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('code', $base)
            ->exists();

        if (! $exists) {
            return $base;
        }

        return $base.'-'.$companyId;
    }

    /**
     * @return EnsureDefaultWarehouseResult
     */
    protected function result(
        int $companyId,
        string $companyName,
        string $action,
        ?int $warehouseId,
        ?string $warehouseName,
        string $note
    ): array {
        return [
            'company_id' => $companyId,
            'company_name' => $companyName,
            'action' => $action,
            'warehouse_id' => $warehouseId,
            'warehouse_name' => $warehouseName,
            'note' => $note,
        ];
    }
}
