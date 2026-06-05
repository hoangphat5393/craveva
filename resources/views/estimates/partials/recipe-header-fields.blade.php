@php
    $e = $estimate ?? null;
    $readOnly = $readOnly ?? false;
@endphp
<div class="col-12 mt-2 mb-2">
    <p class="f-14 text-dark-grey mb-0 font-weight-bold">@lang('modules.estimates.recipeHeaderSection')</p>
    <p class="f-12 text-lightest mb-0">@lang('modules.estimates.recipeHeaderSectionHelp')</p>
</div>
@if ($readOnly)
    <div class="col-md-3 col-sm-6 mb-3">
        <p class="f-12 text-dark-grey mb-0">@lang('modules.estimates.recipeMoq')</p>
        <p class="f-14 text-dark mb-0">{{ $e?->recipe_moq ?? '—' }}</p>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <p class="f-12 text-dark-grey mb-0">@lang('modules.estimates.recipePackaging')</p>
        <p class="f-14 text-dark mb-0">{{ $e?->recipe_packaging ?: '—' }}</p>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <p class="f-12 text-dark-grey mb-0">@lang('modules.estimates.recipeOemSku')</p>
        <p class="f-14 text-dark mb-0">{{ $e?->recipe_oem_sku ?: '—' }}</p>
    </div>
@else
    <div class="col-md-6 col-lg-3">
        <div class="form-group mb-4">
            <x-forms.label fieldId="recipe_moq" :fieldLabel="__('modules.estimates.recipeMoq')" />
            <input type="number" min="0" step="1" name="recipe_moq" id="recipe_moq" class="form-control height-35 f-15" value="{{ $e?->recipe_moq ?? '' }}">
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="form-group mb-4">
            <x-forms.label fieldId="recipe_packaging" :fieldLabel="__('modules.estimates.recipePackaging')" />
            <input type="text" name="recipe_packaging" id="recipe_packaging" class="form-control height-35 f-15" maxlength="255" value="{{ $e?->recipe_packaging ?? '' }}">
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="form-group mb-4">
            <x-forms.label fieldId="recipe_oem_sku" :fieldLabel="__('modules.estimates.recipeOemSku')" />
            <input type="text" name="recipe_oem_sku" id="recipe_oem_sku" class="form-control height-35 f-15" maxlength="128" value="{{ $e?->recipe_oem_sku ?? '' }}">
        </div>
    </div>
@endif
