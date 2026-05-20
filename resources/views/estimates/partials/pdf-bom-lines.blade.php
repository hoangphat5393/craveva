@if (isset($estimate) && $estimate->relationLoaded('bomLines') && $estimate->bomLines->isNotEmpty())
    @php
        $pdfBomTotal = $estimate->bomLines->sum(static fn($line) => (float) ($line->line_total ?? (float) $line->quantity * (float) $line->unit_cost));
    @endphp
    <div class="page_break"></div>
    <h3 class="box-title m-t-20 text-center h3-border">@lang('modules.estimates.bomLinesHeading')</h3>
    <table id="invoice-table" class="description" style="margin-top: 12px;">
        <thead>
            <tr>
                <th class="desc">@lang('modules.estimates.bomMaterial')</th>
                <th class="qty">@lang('modules.invoices.qty')</th>
                <th class="unit">@lang('modules.invoices.unitType')</th>
                <th class="unit">@lang('modules.estimates.bomUnitCost')</th>
                <th class="unit">@lang('modules.estimates.bomLineTotal')</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($estimate->bomLines->sortBy('sort_order') as $line)
                <tr>
                    <td class="desc">
                        {{ $line->product?->name ?? $line->material_name }}
                    </td>
                    <td class="qty">{{ rtrim(rtrim(number_format((float) $line->quantity, 4, '.', ''), '0'), '.') }}</td>
                    <td class="unit">{{ $line->unit?->unit_type ?? '—' }}</td>
                    <td class="unit">{{ currency_format((float) $line->unit_cost, $estimate->currency_id, false) }}</td>
                    <td class="unit">{{ currency_format((float) ($line->line_total ?? (float) $line->quantity * (float) $line->unit_cost), $estimate->currency_id, false) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="desc text-right">@lang('modules.estimates.bomMaterialTotal')</td>
                <td class="unit">{{ currency_format($pdfBomTotal, $estimate->currency_id, false) }}</td>
            </tr>
        </tfoot>
    </table>
@endif
