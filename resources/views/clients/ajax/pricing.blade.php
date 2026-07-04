@php
    $viewClientTiers = user()->permission('view_client_tiers');
    $viewClientPricing = user()->permission('view_client_pricing');
    $canManageClientTiers = $viewClientTiers != 'none' && Route::has('pricing.client_tiers.index');
    $canManageClientPricing = $viewClientPricing != 'none' && Route::has('pricing.client_pricing.index');
@endphp

<div class="row">
    @if ($viewClientTiers != 'none')
        <div class="col-lg-4 col-md-12 mb-4">
            <x-cards.widget :title="__('pricing::app.pricingTier')" :value="$pricingTier?->name ?? '--'" icon="tag" />
        </div>
    @endif

    @if ($viewClientPricing != 'none')
        <div class="col-lg-4 col-md-6 mb-4">
            <x-cards.widget :title="__('pricing::app.menu.contractPricing')" :value="$contractPricingActiveTotal" icon="check-circle" />
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <x-cards.widget :title="__('app.total')" :value="$contractPricingTotal" icon="list" />
        </div>
    @endif
</div>

<div class="row">
    @if ($viewClientTiers != 'none')
        <div class="col-lg-5 col-md-12 mb-4">
            <x-cards.data :title="__('pricing::app.pricingTierDetails')">
                <x-cards.data-row :label="__('pricing::app.tierName')" :value="$pricingTier?->name ?? '--'" />
                <x-cards.data-row :label="__('pricing::app.discountType')" :value="$pricingTier?->discount_type ?? '--'" />
                <x-cards.data-row :label="__('pricing::app.discountValue')" :value="$pricingTier?->discount_value ?? '--'" />
                <x-cards.data-row :label="__('pricing::app.priority')" :value="$pricingTier?->priority ?? '--'" />
                <x-cards.data-row :label="__('app.status')" :value="$pricingTier ? ($pricingTier->is_active ? __('app.active') : __('app.inactive')) : '--'" />

                @if ($canManageClientTiers)
                    <div class="mt-3">
                        <x-forms.link-secondary :link="route('pricing.client_tiers.index')" icon="external-link-alt">
                            @lang('pricing::app.menu.clientTiers')
                        </x-forms.link-secondary>
                    </div>
                @endif
            </x-cards.data>
        </div>
    @endif

    @if ($viewClientPricing != 'none')
        <div class="col-lg-7 col-md-12 mb-4">
            <x-cards.data :title="__('pricing::app.menu.contractPricing')">
                <div class="table-responsive">
                    <table class="table table-hover border-0 mb-0">
                        <thead>
                            <tr>
                                <th>@lang('app.product')</th>
                                <th>@lang('pricing::app.customPrice')</th>
                                <th>@lang('pricing::app.discount')</th>
                                <th>@lang('pricing::app.startDate')</th>
                                <th>@lang('pricing::app.endDate')</th>
                                <th>@lang('app.status')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($contractPricingRows as $row)
                                <tr>
                                    <td>
                                        {{ $row->product?->name ?? '--' }}
                                        @if ($row->product?->sku)
                                            <p class="mb-0 f-11 text-light-grey">{{ $row->product->sku }}</p>
                                        @endif
                                    </td>
                                    <td>{{ $row->custom_price ?? '--' }}</td>
                                    <td>
                                        @if ($row->discount_type || $row->discount_value)
                                            {{ $row->discount_type ?? '--' }} {{ $row->discount_value ?? '' }}
                                        @else
                                            --
                                        @endif
                                    </td>
                                    <td>{{ $row->start_date ? $row->start_date->timezone(company()->timezone)->translatedFormat(company()->date_format) : '--' }}</td>
                                    <td>{{ $row->end_date ? $row->end_date->timezone(company()->timezone)->translatedFormat(company()->date_format) : '--' }}</td>
                                    <td>
                                        @if ($row->is_active)
                                            <span class="badge badge-success">@lang('app.active')</span>
                                        @else
                                            <span class="badge badge-secondary">@lang('app.inactive')</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-lightest">@lang('messages.noRecordFound')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($canManageClientPricing)
                    <div class="mt-3">
                        <x-forms.link-secondary :link="route('pricing.client_pricing.index')" icon="external-link-alt">
                            @lang('pricing::app.menu.contractPricing')
                        </x-forms.link-secondary>
                    </div>
                @endif
            </x-cards.data>
        </div>
    @endif
</div>
