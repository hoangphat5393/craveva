<?php

namespace Modules\Pricing\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ValidatesBulkRowIds
{
    protected function validatedBulkRowIds(Request $request): array
    {
        $request->validate([
            'row_ids' => 'required|string|max:10000',
        ]);

        $ids = collect(explode(',', (string) $request->input('row_ids')))
            ->map(fn ($value) => trim($value))
            ->filter(fn ($value) => preg_match('/^\d+$/', $value) === 1)
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->take(500)
            ->values()
            ->all();

        if (empty($ids)) {
            throw ValidationException::withMessages([
                'row_ids' => __('messages.selectAction'),
            ]);
        }

        return $ids;
    }
}
