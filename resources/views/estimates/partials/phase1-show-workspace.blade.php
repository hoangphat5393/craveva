@php
    /** @var \App\Models\Estimate $estimate */
    $estimate = $estimate ?? ($invoice ?? null);
    $similarRecipes = $similarRecipes ?? [];
    $productionBoms = $productionBoms ?? collect();
@endphp

@if ($estimate && estimates_phase1_review_enabled() && !in_array('client', user_roles()))
    <div class="phase1-oem-workspace mb-4">
        @if ($estimate->isRevisionRequired())
            <x-alert type="warning" class="mb-3 f-13">
                @lang('modules.estimates.revisionRequiredAlert')
            </x-alert>
        @endif

        <div class="row">
            <div class="col-lg-7">
                <div class="card border-grey mb-3">
                    <div class="card-header bg-white border-bottom-grey py-2">
                        <h6 class="mb-0 f-15 font-weight-bold text-dark">@lang('modules.estimates.workspaceZoneRecipe')</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            @include('estimates.partials.recipe-header-fields', ['estimate' => $estimate, 'readOnly' => true])
                        </div>
                        @include('estimates.partials.copy-production-bom', ['productionBoms' => $productionBoms])
                        @include('estimates.partials.similar-recipes', ['similarRecipes' => $similarRecipes])
                        @include('estimates.partials.bom-lines', [
                            'estimate' => $estimate,
                            'readOnly' => true,
                            'productionBoms' => $productionBoms,
                        ])
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-grey mb-3">
                    <div class="card-header bg-white border-bottom-grey py-2">
                        <h6 class="mb-0 f-15 font-weight-bold text-dark">@lang('modules.estimates.workspaceZoneApproval')</h6>
                    </div>
                    <div class="card-body p-3">
                        @include('estimates.partials.internal-review-banner', ['estimate' => $estimate])
                        @include('estimates.partials.approval-timeline', ['estimate' => $estimate])
                    </div>
                </div>
                <div class="card border-grey mb-3">
                    <div class="card-header bg-white border-bottom-grey py-2">
                        <h6 class="mb-0 f-15 font-weight-bold text-dark">@lang('modules.estimates.workspaceZoneFinancial')</h6>
                    </div>
                    <div class="card-body p-3">
                        @include('estimates.partials.margin-summary', ['estimate' => $estimate])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
