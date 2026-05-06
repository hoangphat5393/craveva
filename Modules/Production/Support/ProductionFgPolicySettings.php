<?php

namespace Modules\Production\Support;

final readonly class ProductionFgPolicySettings
{
    /**
     * @param  non-empty-string  $policyMode  strict | controlled | flexible
     */
    public function __construct(
        public string $policyMode,
        public float $tolerancePercent,
        public float $toleranceAbsolute,
        public bool $controlledRequireReasonBeyondTolerance,
        public bool $controlledBlockBeyondTolerance,
        public bool $flexibleRequireReasonWhenOverPlanned,
    ) {}
}
