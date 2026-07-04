<?php

use App\Models\ClientDetails;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$redundantFieldNames = [
    'payment_terms',
    'channel_type',
    'business_type',
    'business_closure_date',
];

$options = parseOptions($argv);

if (isset($options['help'])) {
    printHelp();
    exit(0);
}

$force = isset($options['force']);
$exceptCompanyIds = normalizeCompanyIds($options['except-company'] ?? []);
$onlyCompanyIds = normalizeCompanyIds($options['only-company'] ?? []);

if ($onlyCompanyIds !== [] && $exceptCompanyIds !== []) {
    fwrite(STDERR, "Use either --only-company or --except-company, not both.\n");
    exit(1);
}

if (! $force) {
    echo "DRY RUN only. Re-run with --force to delete matched custom_fields_data and custom_fields.\n\n";
}

$query = DB::table('custom_fields as cf')
    ->join('custom_field_groups as cfg', 'cfg.id', '=', 'cf.custom_field_group_id')
    ->leftJoin('custom_fields_data as cfd', 'cfd.custom_field_id', '=', 'cf.id')
    ->where('cfg.name', 'Client')
    ->where('cfg.model', ClientDetails::CUSTOM_FIELD_MODEL)
    ->whereIn('cf.name', $redundantFieldNames)
    ->select([
        'cf.id',
        'cf.name',
        'cf.label',
        'cf.company_id',
        'cfg.company_id as group_company_id',
        DB::raw('COUNT(cfd.id) as data_rows'),
    ])
    ->groupBy('cf.id', 'cf.name', 'cf.label', 'cf.company_id', 'cfg.company_id')
    ->orderBy('cf.company_id')
    ->orderBy('cf.name');

if ($onlyCompanyIds !== []) {
    $query->whereIn('cf.company_id', $onlyCompanyIds);
    $query->whereIn('cfg.company_id', $onlyCompanyIds);
}

if ($exceptCompanyIds !== []) {
    $query->whereNotIn('cf.company_id', $exceptCompanyIds);
    $query->whereNotIn('cfg.company_id', $exceptCompanyIds);
}

$fields = $query->get();

if ($fields->isEmpty()) {
    echo "No redundant Client custom fields found.\n";
    exit(0);
}

printRows($fields);

$fieldIds = $fields->pluck('id')->map(fn ($id): int => (int) $id)->all();
$dataRows = (int) $fields->sum('data_rows');

printf(
    "\nMatched %d custom_fields and %d custom_fields_data rows.\n",
    count($fieldIds),
    $dataRows
);

if (! $force) {
    exit(0);
}

DB::transaction(function () use ($fieldIds, $redundantFieldNames): void {
    DB::table('custom_fields_data')
        ->whereIn('custom_field_id', $fieldIds)
        ->delete();

    DB::table('custom_fields')
        ->whereIn('id', $fieldIds)
        ->whereIn('name', $redundantFieldNames)
        ->delete();
});

echo "Deleted matched redundant Client custom field data and definitions.\n";

/**
 * @param  array<int, string>  $argv
 * @return array<string, bool|list<string>>
 */
function parseOptions(array $argv): array
{
    $options = [];

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--help' || $arg === '-h') {
            $options['help'] = true;

            continue;
        }

        if ($arg === '--force') {
            $options['force'] = true;

            continue;
        }

        if (str_starts_with($arg, '--except-company=')) {
            $options['except-company'][] = substr($arg, strlen('--except-company='));

            continue;
        }

        if (str_starts_with($arg, '--only-company=')) {
            $options['only-company'][] = substr($arg, strlen('--only-company='));

            continue;
        }

        fwrite(STDERR, "Unknown option: {$arg}\n");
        printHelp();
        exit(1);
    }

    return $options;
}

/**
 * @param  list<string>|bool  $values
 * @return list<int>
 */
function normalizeCompanyIds(array|bool $values): array
{
    if ($values === false) {
        return [];
    }

    return collect($values)
        ->flatMap(fn (string $value): array => explode(',', $value))
        ->map(fn (string $value): int => (int) trim($value))
        ->filter(fn (int $value): bool => $value > 0)
        ->unique()
        ->values()
        ->all();
}

function printHelp(): void
{
    echo <<<HELP
Usage:
  php scripts/cleanup_redundant_client_custom_fields.php [options]

Options:
  --except-company=ID[,ID]  Exclude company ids from cleanup. Can be repeated.
  --only-company=ID[,ID]    Only check/delete these company ids. Can be repeated.
  --force                  Delete matched rows. Without this option, script is dry-run only.
  --help                   Show this help.

Target fields:
  payment_terms, channel_type, business_type, business_closure_date

Safety scope:
  Only custom fields in the Client custom field group for App\Models\ClientDetails are matched.

HELP;
}

function printRows($fields): void
{
    $headers = ['field_id', 'company_id', 'group_company_id', 'name', 'label', 'data_rows'];
    $rows = $fields->map(fn ($field): array => [
        (string) $field->id,
        (string) $field->company_id,
        (string) $field->group_company_id,
        (string) $field->name,
        (string) $field->label,
        (string) $field->data_rows,
    ])->all();

    $widths = [];

    foreach ($headers as $index => $header) {
        $widths[$index] = strlen($header);
    }

    foreach ($rows as $row) {
        foreach ($row as $index => $value) {
            $widths[$index] = max($widths[$index], strlen($value));
        }
    }

    $printLine = function (array $columns) use ($widths): void {
        $parts = [];

        foreach ($columns as $index => $value) {
            $parts[] = str_pad($value, $widths[$index]);
        }

        echo implode(' | ', $parts)."\n";
    };

    $printLine($headers);
    echo implode('-+-', array_map(fn (int $width): string => str_repeat('-', $width), $widths))."\n";

    foreach ($rows as $row) {
        $printLine($row);
    }
}
