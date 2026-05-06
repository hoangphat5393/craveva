<?php

namespace Modules\Production\Services;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Modules\Production\Entities\ProductionBatchOutput;
use Modules\Production\Entities\ProductionCompanyFgPolicy;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Support\ProductionFgPolicySettings;

/**
 * Enforces planned vs registered FG totals at the {@see ProductionOrder} level (sums all batch outputs).
 */
class ProductionFgQuantityPolicyService
{
    public const MODE_STRICT = 'strict';

    public const MODE_CONTROLLED = 'controlled';

    public const MODE_FLEXIBLE = 'flexible';

    public function resolveForCompany(int $companyId): ProductionFgPolicySettings
    {
        /** @var array<string, mixed> $defaults */
        $defaults = self::defaultsArray();

        $row = ProductionCompanyFgPolicy::query()->where('company_id', $companyId)->first();

        return new ProductionFgPolicySettings(
            policyMode: self::normalizeMode($row !== null ? (string) $row->policy_mode : (string) $defaults['policy_mode']),
            tolerancePercent: self::positiveOrZeroFloat($row?->tolerance_percent ?? $defaults['tolerance_percent']),
            toleranceAbsolute: self::positiveOrZeroFloat($row?->tolerance_absolute ?? $defaults['tolerance_absolute']),
            controlledRequireReasonBeyondTolerance: (bool) ($row?->controlled_require_reason_beyond_tolerance ?? $defaults['controlled_require_reason_beyond_tolerance']),
            controlledBlockBeyondTolerance: (bool) ($row?->controlled_block_beyond_tolerance ?? $defaults['controlled_block_beyond_tolerance']),
            flexibleRequireReasonWhenOverPlanned: (bool) $defaults['flexible_require_reason_when_over_planned'],
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function assertProjectedTotalsAllowedForOrder(
        ProductionOrder $order,
        float $registeredFgTotalIncludingPendingLines,
        ?string $varianceReason = null,
    ): void {
        $companyId = (int) ($order->company_id ?? 0);
        $settings = $companyId > 0
            ? $this->resolveForCompany($companyId)
            : $this->fallbackSettingsFromConfig();

        $planned = (float) $order->planned_quantity;
        $projected = self::quantizeQty(max(0.0, $registeredFgTotalIncludingPendingLines));
        $reason = trim((string) ($varianceReason ?? ''));
        $qtyPlanned = self::quantizeQty($planned);

        if ($planned < 0) {
            throw new InvalidArgumentException(__('production::app.fgPlannedQtyInvalid'));
        }

        switch ($settings->policyMode) {
            case self::MODE_STRICT:
                if ($projected > $qtyPlanned) {
                    throw new InvalidArgumentException(__('production::app.fgStrictExceedsPlanned', [
                        'planned' => $planned,
                        'total' => $projected,
                    ]));
                }

                break;

            case self::MODE_CONTROLLED:
                if ($projected <= $qtyPlanned) {
                    break;
                }

                $ceiling = self::quantizeQty(
                    $qtyPlanned + max(
                        $qtyPlanned * ($settings->tolerancePercent / 100.0),
                        self::quantizeQty((float) $settings->toleranceAbsolute),
                    ),
                );

                if ($projected <= $ceiling) {
                    break;
                }

                if ($settings->controlledBlockBeyondTolerance) {
                    throw new InvalidArgumentException(__('production::app.fgControlledBeyondToleranceBlocked', [
                        'planned' => $planned,
                        'total' => $projected,
                        'allowed' => $ceiling,
                    ]));
                }

                if ($settings->controlledRequireReasonBeyondTolerance && $reason === '') {
                    throw new InvalidArgumentException(__('production::app.fgControlledBeyondToleranceRequiresReason'));
                }

                break;

            case self::MODE_FLEXIBLE:
                if ($projected > $qtyPlanned && $settings->flexibleRequireReasonWhenOverPlanned && $reason === '') {
                    throw new InvalidArgumentException(__('production::app.fgFlexibleOverPlannedRequiresReason'));
                }

                break;

            default:
                throw new InvalidArgumentException(__('production::app.fgUnknownPolicyMode'));
        }
    }

    public function registeredFgTotalForOrder(ProductionOrder $order): float
    {
        $batchIds = $order->batches()->pluck('id');

        if ($batchIds->isEmpty()) {
            return 0.0;
        }

        return self::quantizeQty(max(0.0, (float) ProductionBatchOutput::query()
            ->whereIn('production_batch_id', $batchIds)
            ->sum('quantity')));
    }

    public function varianceSnapshot(float $plannedQuantity, float $projectedFgTotalAfterLine): array
    {
        $planned = max(0.0, self::quantizeQty((float) $plannedQuantity));
        $projected = max(0.0, self::quantizeQty((float) $projectedFgTotalAfterLine));

        $fromPlanned = self::quantizeQty($projected - $planned);

        $percent = $planned > 0.00000001
            ? (($projected - $planned) / $planned) * 100.0
            : null;

        return [
            'variance_from_planned_total' => $fromPlanned,
            'variance_from_planned_percent' => $percent !== null ? self::quantizeQty((float) $percent) : null,
        ];
    }

    public function outputRequiresVarianceApproval(ProductionOrder $order, ProductionBatchOutput $output): bool
    {
        if (! (bool) Config::get('production.phase2.enforce_variance_approval', false)) {
            return false;
        }

        $companyId = (int) ($order->company_id ?? 0);
        $settings = $companyId > 0
            ? $this->resolveForCompany($companyId)
            : $this->fallbackSettingsFromConfig();

        $variance = (float) ($output->variance_from_planned_total ?? 0);
        $planned = max(0.0, self::quantizeQty((float) $order->planned_quantity));
        $projected = self::quantizeQty($planned + $variance);

        if ($settings->policyMode === self::MODE_STRICT) {
            return false;
        }

        if ($settings->policyMode === self::MODE_CONTROLLED) {
            if ($projected <= $planned) {
                return false;
            }

            $ceiling = self::quantizeQty(
                $planned + max(
                    $planned * ($settings->tolerancePercent / 100.0),
                    self::quantizeQty((float) $settings->toleranceAbsolute),
                ),
            );

            return $projected > $ceiling;
        }

        return $projected > $planned;
    }

    /**
     * @return array<string, mixed>
     */
    public static function mergedDefaultsFromConfig(): array
    {
        return self::defaultsArray();
    }

    protected function fallbackSettingsFromConfig(): ProductionFgPolicySettings
    {
        $defaults = self::defaultsArray();

        return new ProductionFgPolicySettings(
            policyMode: self::normalizeMode((string) $defaults['policy_mode']),
            tolerancePercent: self::positiveOrZeroFloat($defaults['tolerance_percent']),
            toleranceAbsolute: self::positiveOrZeroFloat($defaults['tolerance_absolute']),
            controlledRequireReasonBeyondTolerance: (bool) $defaults['controlled_require_reason_beyond_tolerance'],
            controlledBlockBeyondTolerance: (bool) $defaults['controlled_block_beyond_tolerance'],
            flexibleRequireReasonWhenOverPlanned: (bool) $defaults['flexible_require_reason_when_over_planned'],
        );
    }

    /**
     * @return array{
     *     policy_mode: string,
     *     tolerance_percent: float,
     *     tolerance_absolute: float,
     *     controlled_require_reason_beyond_tolerance: bool,
     *     controlled_block_beyond_tolerance: bool,
     *     flexible_require_reason_when_over_planned: bool
     * }
     */
    protected static function defaultsArray(): array
    {
        /** @var mixed $configured */
        $configured = Config::get('production.fg_quantity_policy.defaults');

        $base = [
            'policy_mode' => self::MODE_CONTROLLED,
            'tolerance_percent' => 5.0,
            'tolerance_absolute' => 0.0,
            'controlled_require_reason_beyond_tolerance' => true,
            'controlled_block_beyond_tolerance' => false,
            'flexible_require_reason_when_over_planned' => true,
        ];

        if (! is_array($configured)) {
            return $base;
        }

        /** @var array<string, mixed> $configured */
        return array_merge($base, $configured);
    }

    protected static function normalizeMode(string $mode): string
    {
        $mode = strtolower(trim($mode));

        if (in_array($mode, [
            self::MODE_STRICT,
            self::MODE_CONTROLLED,
            self::MODE_FLEXIBLE,
        ], true)) {
            return $mode;
        }

        return self::MODE_CONTROLLED;
    }

    protected static function positiveOrZeroFloat(mixed $value): float
    {
        $float = is_numeric($value) ? (float) $value : 0.0;

        return $float >= 0 ? self::quantizeQty($float) : 0.0;
    }

    protected static function quantizeQty(float $value): float
    {
        return round($value, 4);
    }
}
