<?php

namespace App\Console\Commands;

use App\Models\Estimate;
use App\Services\Estimates\EstimateTotalsCalculator;
use Illuminate\Console\Command;

class RecalculateEstimateTotalsCommand extends Command
{
    protected $signature = 'estimates:recalculate-totals
                            {--company= : Limit to a company ID}
                            {--estimate= : Recalculate a single estimate ID}
                            {--dry-run : Show changes without saving}';

    protected $description = 'Recalculate estimate sub_total and total from line items (fixes stale header totals)';

    public function handle(EstimateTotalsCalculator $calculator): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $companyId = $this->option('company');
        $estimateId = $this->option('estimate');

        $query = Estimate::query()->with(['items' => fn ($q) => $q->where('type', 'item')->orderBy('field_order')]);

        if ($estimateId !== null && $estimateId !== '') {
            $query->where('id', (int) $estimateId);
        }

        if ($companyId !== null && $companyId !== '') {
            $query->where('company_id', (int) $companyId);
        }

        $updated = 0;
        $skipped = 0;

        $query->orderBy('id')->chunkById(100, function ($estimates) use ($calculator, $dryRun, &$updated, &$skipped): void {
            foreach ($estimates as $estimate) {
                if (! $calculator->totalsAreOutOfSync($estimate)) {
                    $skipped++;

                    continue;
                }

                $calculated = $calculator->calculateForEstimate($estimate);

                $this->line(sprintf(
                    'Estimate #%d (%s): sub_total %s → %s, total %s → %s',
                    $estimate->id,
                    $estimate->estimate_number,
                    $estimate->sub_total,
                    $calculated['sub_total'],
                    $estimate->total,
                    $calculated['total'],
                ));

                if (! $dryRun) {
                    $estimate->sub_total = $calculated['sub_total'];
                    $estimate->total = $calculated['total'];
                    $estimate->save();
                }

                $updated++;
            }
        });

        $this->info(sprintf('Done. %d updated, %d already in sync.%s', $updated, $skipped, $dryRun ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }
}
