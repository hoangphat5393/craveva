<?php

declare(strict_types=1);

namespace App\Services\Estimates;

use App\Models\Estimate;
use App\Scopes\CompanyScope;

final class EstimateSimilarRecipeSearch
{
    public function __construct(
        private readonly EstimateRecipeMarginSummary $marginSummary = new EstimateRecipeMarginSummary,
    ) {}

    /**
     * @return list<array{
     *     estimate_id: int,
     *     estimate_number: string,
     *     client_name: string|null,
     *     recipe_moq: string|int|null,
     *     match_score: int,
     *     gross_margin_percent: float|null,
     *     url: string
     * }>
     */
    public function findForEstimate(Estimate $estimate, int $limit = 5): array
    {
        $estimate->loadMissing(['bomLines', 'client']);

        $productIds = $estimate->bomLines
            ->pluck('product_id')
            ->filter(static fn ($id): bool => $id !== null && (int) $id > 0)
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($productIds === []) {
            return [];
        }

        $candidates = Estimate::withoutGlobalScope(CompanyScope::class)
            ->with(['bomLines', 'client'])
            ->where('company_id', $estimate->company_id)
            ->where('id', '<>', $estimate->id)
            ->whereHas('bomLines', static function ($query) use ($productIds): void {
                $query->whereIn('product_id', $productIds);
            })
            ->orderByDesc('id')
            ->limit(40)
            ->get();

        return $candidates
            ->map(function (Estimate $candidate) use ($productIds): array {
                $candidateProductIds = $candidate->bomLines
                    ->pluck('product_id')
                    ->filter(static fn ($id): bool => $id !== null && (int) $id > 0)
                    ->map(static fn ($id): int => (int) $id)
                    ->unique();

                $intersection = $candidateProductIds->intersect($productIds)->count();
                $union = $candidateProductIds->merge($productIds)->unique()->count();
                $score = $union > 0 ? (int) round(($intersection / $union) * 100) : 0;

                $summary = $this->marginSummary->summarize($candidate);

                return [
                    'estimate_id' => (int) $candidate->id,
                    'estimate_number' => (string) $candidate->estimate_number,
                    'client_name' => $candidate->client?->name,
                    'recipe_moq' => $candidate->recipe_moq,
                    'match_score' => $score,
                    'gross_margin_percent' => $summary['gross_margin_percent'],
                    'url' => route('estimates.show', $candidate->id),
                ];
            })
            ->sortByDesc('match_score')
            ->take($limit)
            ->values()
            ->all();
    }
}
