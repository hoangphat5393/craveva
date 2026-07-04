@extends('layouts.app')

@section('content')
    <div class="w-100 d-flex ">
        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                        @lang($pageTitle)</h2>
                </div>
            </x-slot>

            @php
                /** @var array<string, mixed> $fgPolicySettings */
                $modes = [
                    \Modules\Production\Services\ProductionFgQuantityPolicyService::MODE_STRICT => __('production::app.fgPolicyModeStrict'),
                    \Modules\Production\Services\ProductionFgQuantityPolicyService::MODE_CONTROLLED => __('production::app.fgPolicyModeControlled'),
                    \Modules\Production\Services\ProductionFgQuantityPolicyService::MODE_FLEXIBLE => __('production::app.fgPolicyModeFlexible'),
                ];
                $yieldUomShadowEnabled = $yieldUomShadowEnabled ?? (bool) config('production.phase2.yield_uom_shadow_enabled', false);
            @endphp

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
                <h5 class="f-16 font-weight-bold text-dark-grey mb-2">@lang('production::app.productionSettingsFgPolicySection')</h5>
                <p class="text-lightest f-14 mb-4">@lang('production::app.productionSettingsFgPolicyHelp')</p>

                <div class="row">
                    <div class="col-lg-8 mb-3">
                        <x-forms.select fieldId="policy_mode" :fieldLabel="__('production::app.fgPolicyMode')" fieldName="policy_mode" fieldRequired="true">
                            @foreach ($modes as $value => $label)
                                <option value="{{ $value }}" @selected((string) ($fgPolicySettings['policy_mode'] ?? '') === (string) $value)>
                                    {{ $label }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                </div>

                <p class="text-lightest f-12 mb-3" id="fg-policy-controlled-hint">@lang('production::app.productionSettingsToleranceControlledOnly')</p>

                <div class="row" id="fg-policy-controlled-fields">
                    <div class="col-lg-4 mb-3">
                        <x-forms.label fieldId="tolerance_percent" :fieldLabel="__('production::app.fgTolerancePercent')" fieldRequired="true" />
                        <input type="number" step="0.0001" min="0" max="100" name="tolerance_percent" id="tolerance_percent" class="form-control height-35 f-14" required value="{{ old('tolerance_percent', $fgPolicySettings['tolerance_percent'] ?? 5) }}">
                    </div>
                    <div class="col-lg-4 mb-3">
                        <x-forms.label fieldId="tolerance_absolute" :fieldLabel="__('production::app.fgToleranceAbsolute')" fieldRequired="true" />
                        <input type="number" step="0.0001" min="0" name="tolerance_absolute" id="tolerance_absolute" class="form-control height-35 f-14" required value="{{ old('tolerance_absolute', $fgPolicySettings['tolerance_absolute'] ?? 0) }}">
                    </div>
                </div>

                <div class="row" id="fg-policy-controlled-checkboxes">
                    <div class="col-lg-6 mb-2">
                        <input type="hidden" name="controlled_require_reason_beyond_tolerance" value="0" />
                        <x-forms.checkbox :checked="!empty($fgPolicySettings['controlled_require_reason_beyond_tolerance'])" :fieldLabel="__('production::app.fgControlledRequireReasonBeyondTolerance')" fieldName="controlled_require_reason_beyond_tolerance" fieldId="controlled_require_reason_beyond_tolerance" fieldValue="1" />
                    </div>
                    <div class="col-lg-6 mb-2">
                        <input type="hidden" name="controlled_block_beyond_tolerance" value="0" />
                        <x-forms.checkbox :checked="!empty($fgPolicySettings['controlled_block_beyond_tolerance'])" :fieldLabel="__('production::app.fgControlledBlockBeyondTolerance')" fieldName="controlled_block_beyond_tolerance" fieldId="controlled_block_beyond_tolerance" fieldValue="1" />
                    </div>
                </div>

                <p class="text-lightest f-12 mb-4">@lang('production::app.fgFlexibleReasonFromConfigHelp')</p>

                <div class="alert alert-secondary f-13 mb-0">
                    <strong class="d-block mb-1">@lang('production::app.productionSettingsYieldShadowNoteTitle')</strong>
                    <p class="mb-1">
                        @if ($yieldUomShadowEnabled)
                            @lang('production::app.productionSettingsYieldShadowStatusOn')
                        @else
                            @lang('production::app.productionSettingsYieldShadowStatusOff')
                        @endif
                    </p>
                    <p class="mb-0 text-muted f-12">@lang('production::app.productionSettingsYieldShadowConfigHint')</p>
                </div>
            </div>

            <x-slot name="action">
                <div class="w-100 border-top-grey">
                    <x-setting-form-actions>
                        <x-forms.button-primary id="save-production-fg-policy" class="mr-3" icon="check">@lang('app.save')
                        </x-forms.button-primary>
                    </x-setting-form-actions>
                </div>
            </x-slot>
        </x-setting-card>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const modeSelect = document.getElementById('policy_mode');
            const controlledBlock = document.getElementById('fg-policy-controlled-fields');
            const controlledCheckboxes = document.getElementById('fg-policy-controlled-checkboxes');
            const controlledHint = document.getElementById('fg-policy-controlled-hint');

            const syncControlledVisibility = () => {
                const isControlled = modeSelect && modeSelect.value === 'controlled';
                const display = isControlled ? '' : 'none';
                if (controlledBlock) {
                    controlledBlock.style.display = display;
                }
                if (controlledCheckboxes) {
                    controlledCheckboxes.style.display = display;
                }
                if (controlledHint) {
                    controlledHint.style.display = display;
                }
            };

            if (modeSelect) {
                modeSelect.addEventListener('change', syncControlledVisibility);
                if (window.jQuery && typeof window.jQuery.fn.selectpicker === 'function') {
                    window.jQuery(modeSelect).on('changed.bs.select', syncControlledVisibility);
                }
                syncControlledVisibility();
            }
        })();

        $('#save-production-fg-policy').click(function() {
            const button = $(this);
            button.prop('disabled', true);
            $.easyBlockUI('#editSettings');

            window.apiHttp.postUrlEncoded("{{ route('production.fg-quantity-policy.update') }}", $('#editSettings').serialize())
                .catch(function(err) {
                    $.handleApiFormError(err);
                })
                .finally(function() {
                    button.prop('disabled', false);
                    $.easyUnblockUI('#editSettings');
                });
        });
    </script>
@endpush
