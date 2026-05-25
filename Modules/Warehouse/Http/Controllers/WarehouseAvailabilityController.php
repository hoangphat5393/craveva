<?php

namespace Modules\Warehouse\Http\Controllers;

use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Warehouse\Services\WarehouseAvailabilityService;

class WarehouseAvailabilityController
{
    public function __construct(
        protected WarehouseAvailabilityService $availabilityService
    ) {}

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|min:1',
            'warehouse_ids' => 'nullable|array',
            'warehouse_ids.*' => 'integer|min:1',
            'company_id' => 'nullable|integer|min:1',
        ]);

        $companyId = $this->resolveAuthorizedCompanyId($request, $validated['company_id'] ?? null);

        $result = $this->availabilityService->availabilityByProduct(
            $companyId,
            (int) $validated['product_id'],
            array_values(array_map('intval', $validated['warehouse_ids'] ?? []))
        );

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    /**
     * Never trust a bare client-supplied company id: map the authenticated principal to allowed companies.
     *
     * @throws ValidationException when the account has multiple companies and `company_id` is missing
     */
    private function resolveAuthorizedCompanyId(Request $request, ?int $requestedCompanyId): int
    {
        $auth = $request->user();

        if ($auth instanceof User) {
            $cid = $auth->company_id;
            if ($cid === null || (int) $cid <= 0) {
                $this->denyWarehouseAvailability();
            }
            $cid = (int) $cid;
            if ($requestedCompanyId !== null && $requestedCompanyId !== $cid) {
                $this->denyWarehouseAvailability();
            }

            return $cid;
        }

        if ($auth instanceof UserAuth) {
            $allowed = DB::table('users')
                ->where('user_auth_id', $auth->id)
                ->whereNotNull('company_id')
                ->pluck('company_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            if ($allowed === []) {
                $this->denyWarehouseAvailability();
            }

            if (count($allowed) === 1) {
                if ($requestedCompanyId !== null && $requestedCompanyId !== $allowed[0]) {
                    $this->denyWarehouseAvailability();
                }

                return $allowed[0];
            }

            if ($requestedCompanyId === null || $requestedCompanyId <= 0) {
                throw ValidationException::withMessages([
                    'company_id' => __('validation.required', ['attribute' => 'company id']),
                ]);
            }

            if (! in_array($requestedCompanyId, $allowed, true)) {
                $this->denyWarehouseAvailability();
            }

            return $requestedCompanyId;
        }

        $this->denyWarehouseAvailability();
    }

    /**
     * Use an explicit JSON response so API clients receive HTTP 403 (some global handlers remap {@see abort()} to 500 for JSON).
     */
    private function denyWarehouseAvailability(): never
    {
        throw new HttpResponseException(response()->json([
            'message' => __('warehouse::app.err_permission_denied'),
        ], 403));
    }
}
