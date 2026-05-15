<?php

namespace Modules\Production\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionBomItem;
use Modules\Production\Http\Requests\StoreProductionBomRequest;
use Modules\Production\Http\Requests\UpdateProductionBomRequest;
use Modules\Production\Support\ProductionProductUnitLabelMap;
use Modules\Production\Support\ProductionTenantAccess;

class ProductionBomController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_if(! ProductionTenantAccess::tenantMayUseProduction(), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $this->assertViewProductionBoms();

        $this->pageTitle = __('production::app.menuBillOfMaterials');
        $companyId = (int) company()->id;

        $query = ProductionBom::query()
            ->with(['outputProduct'])
            ->where('company_id', $companyId)
            ->withCount(['items'])
            ->orderByDesc('id');

        if ($request->filled('output_product_id')) {
            $query->where('output_product_id', (int) $request->input('output_product_id'));
        }

        $this->boms = $query->paginate(25)->withQueryString();

        $pageOutputProducts = $this->boms->getCollection()
            ->map(static fn (ProductionBom $bom): ?Product => $bom->outputProduct)
            ->filter()
            ->unique('id')
            ->values();

        $this->bomListFgUnitByProductId = $pageOutputProducts->isEmpty()
            ? collect()
            : ProductionProductUnitLabelMap::forProducts($pageOutputProducts, $companyId);

        $this->finishedGoodsFilter = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->forBomOutput()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('production::boms.index', $this->data);
    }

    public function create(): View
    {
        $this->assertAddProductionBoms();
        $this->pageTitle = __('production::app.newBom');

        $this->addProductData();

        return view('production::boms.create', $this->data);
    }

    public function store(StoreProductionBomRequest $request): RedirectResponse
    {
        $this->assertAddProductionBoms();

        $companyId = (int) company()->id;
        $validated = $request->validated();

        $bom = DB::transaction(function () use ($validated, $companyId): ProductionBom {
            $bom = ProductionBom::query()->create([
                'company_id' => $companyId,
                'output_product_id' => (int) $validated['output_product_id'],
                'version' => (string) $validated['version'],
                'code' => $validated['code'] ?? null,
                'effective_from' => $validated['effective_from'] ?? null,
                'effective_to' => $validated['effective_to'] ?? null,
                'is_default' => $validated['is_default'] ?? false,
                'notes' => $validated['notes'] ?? null,
                'created_by' => user()?->id,
                'updated_by' => user()?->id,
            ]);

            $this->syncOnlyDefaultFlag($companyId, (int) $validated['output_product_id'], $bom);

            foreach ($validated['items'] as $index => $line) {
                ProductionBomItem::query()->create([
                    'company_id' => $companyId,
                    'production_bom_id' => $bom->id,
                    'component_product_id' => (int) $line['component_product_id'],
                    'quantity' => (float) $line['quantity'],
                    'unit_id' => isset($line['unit_id']) ? (int) $line['unit_id'] : null,
                    'yield_factor' => isset($line['yield_factor']) ? (float) $line['yield_factor'] : null,
                    'sort_order' => (int) $index,
                ]);
            }

            return $bom;
        });

        return redirect()
            ->route('production.boms.show', $bom)
            ->with('success', __('messages.recordSaved'));
    }

    public function show(ProductionBom $bom): View
    {
        $this->assertViewProductionBoms();
        $this->assertBomInCompany($bom);

        $bom->load(['items.componentProduct.unit', 'outputProduct.unit']);

        $this->pageTitle = __('production::app.bomDetail').' '.$bom->version;
        $this->bom = $bom;

        return view('production::boms.show', $this->data);
    }

    public function edit(ProductionBom $bom): View
    {
        $this->assertEditProductionBoms();
        $this->assertBomInCompany($bom);
        abort_if(! $this->bomIsEditable($bom), 403);

        $bom->load(['items']);

        $this->pageTitle = __('production::app.editBom');
        $this->bom = $bom;

        $this->addProductData();

        return view('production::boms.edit', $this->data);
    }

    public function update(UpdateProductionBomRequest $request, ProductionBom $bom): RedirectResponse
    {
        $this->assertEditProductionBoms();
        $this->assertBomInCompany($bom);
        abort_if(! $this->bomIsEditable($bom), 403);

        $companyId = (int) company()->id;
        $validated = $request->validated();

        DB::transaction(function () use ($bom, $validated, $companyId): void {
            $bom->update([
                'output_product_id' => (int) $validated['output_product_id'],
                'version' => (string) $validated['version'],
                'code' => $validated['code'] ?? null,
                'effective_from' => $validated['effective_from'] ?? null,
                'effective_to' => $validated['effective_to'] ?? null,
                'is_default' => $validated['is_default'] ?? false,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => user()?->id,
            ]);

            $this->syncOnlyDefaultFlag($companyId, (int) $validated['output_product_id'], $bom);

            $bom->items()->delete();

            foreach ($validated['items'] as $index => $line) {
                ProductionBomItem::query()->create([
                    'company_id' => $companyId,
                    'production_bom_id' => $bom->id,
                    'component_product_id' => (int) $line['component_product_id'],
                    'quantity' => (float) $line['quantity'],
                    'unit_id' => isset($line['unit_id']) ? (int) $line['unit_id'] : null,
                    'yield_factor' => isset($line['yield_factor']) ? (float) $line['yield_factor'] : null,
                    'sort_order' => (int) $index,
                ]);
            }
        });

        return redirect()
            ->route('production.boms.show', $bom)
            ->with('success', __('messages.updateSuccess'));
    }

    public function destroy(ProductionBom $bom): RedirectResponse
    {
        $this->assertEditProductionBoms();
        $this->assertBomInCompany($bom);
        abort_if(! $this->bomIsEditable($bom), 403);

        $bom->items()->delete();
        $bom->delete();

        return redirect()
            ->route('production.boms.index')
            ->with('success', __('messages.deleteSuccess'));
    }

    protected function syncOnlyDefaultFlag(int $companyId, int $outputProductId, ProductionBom $bom): void
    {
        if (! $bom->is_default) {
            return;
        }

        ProductionBom::query()
            ->where('company_id', $companyId)
            ->where('output_product_id', $outputProductId)
            ->where('id', '<>', $bom->id)
            ->update(['is_default' => false]);
    }

    protected function bomIsEditable(ProductionBom $bom): bool
    {
        return ! $bom->productionOrders()->exists();
    }

    protected function assertBomInCompany(ProductionBom $bom): void
    {
        abort_if((int) $bom->company_id !== (int) company()->id, 403);
    }

    protected function assertViewProductionBoms(): void
    {
        $p = user()->permission('view_production_orders');
        abort_if(! in_array($p, ['all', 'added', 'owned', 'both'], true), 403);
    }

    protected function assertAddProductionBoms(): void
    {
        $p = user()->permission('add_production_orders');
        abort_if(! in_array($p, ['all', 'added', 'owned', 'both'], true), 403);
    }

    protected function assertEditProductionBoms(): void
    {
        $p = user()->permission('edit_production_orders');
        abort_if(! in_array($p, ['all', 'added', 'owned', 'both'], true), 403);
    }

    protected function addProductData(): void
    {
        $companyId = (int) company()->id;

        $this->finishedGoods = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->forBomOutput()
            ->with('unit:id,unit_type')
            ->orderBy('name')
            ->get(['id', 'name', 'unit_id']);

        $this->componentProducts = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->forBomComponents()
            ->with('unit:id,unit_type')
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'name', 'unit_id', 'type']);

        $this->componentProductsByType = $this->componentProducts->groupBy('type');

        $this->bomFgUnitByProductId = ProductionProductUnitLabelMap::forProducts($this->finishedGoods, $companyId);
        $this->bomComponentUnitByProductId = ProductionProductUnitLabelMap::forProducts($this->componentProducts, $companyId);
    }
}
