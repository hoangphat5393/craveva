<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $batch->batch_code }} — {{ __('production::app.printLabelSlipPageHeading') }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; margin: 24px; color: #111; }
        h1 { font-size: 22px; margin: 0 0 8px; }
        .code { font-size: 28px; font-weight: 700; letter-spacing: 0.04em; margin: 12px 0 20px; }
        table { border-collapse: collapse; width: 100%; max-width: 520px; }
        th, td { text-align: left; padding: 8px 10px; border: 1px solid #ccc; font-size: 14px; }
        th { background: #f4f4f4; width: 38%; }
        .hint { margin-top: 20px; font-size: 12px; color: #555; }
        @media print {
            body { margin: 12mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <p class="no-print"><button type="button" onclick="window.print()">{{ __('production::app.printLabelSlipOpen') }} (window.print)</button></p>
    <h1>{{ __('production::app.printLabelSlipPageHeading') }}</h1>
    <div class="code">{{ $batch->batch_code }}</div>
    <table>
        <tr>
            <th>{{ __('production::app.printLabelSlipCompany') }}</th>
            <td>{{ $companyName }}</td>
        </tr>
        <tr>
            <th>{{ __('production::app.printLabelSlipOrder') }}</th>
            <td>#{{ $order->id }}</td>
        </tr>
        <tr>
            <th>{{ __('production::app.batchCode') }}</th>
            <td>{{ $batch->batch_code }}</td>
        </tr>
        <tr>
            <th>{{ __('production::app.fgProduct') }}</th>
            <td>{{ $order->outputProduct?->name ?? '—' }}</td>
        </tr>
        <tr>
            <th>{{ __('production::app.printLabelSlipPlannedQty') }}</th>
            <td>{{ rtrim(rtrim(number_format((float) $order->planned_quantity, 4, '.', ''), '0'), '.') }}</td>
        </tr>
        <tr>
            <th>{{ __('production::app.printLabelSlipPrintedAt') }}</th>
            <td>{{ $printedAt->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</td>
        </tr>
    </table>
    <p class="hint">{{ __('production::app.printLabelSlipInstructions') }}</p>
</body>
</html>
