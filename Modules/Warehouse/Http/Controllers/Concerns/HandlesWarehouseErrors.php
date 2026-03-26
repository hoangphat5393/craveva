<?php

namespace Modules\Warehouse\Http\Controllers\Concerns;

use App\Helper\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Warehouse\Exceptions\WarehouseBusinessException;

trait HandlesWarehouseErrors
{
    protected function warehouseCompanyId(): ?int
    {
        $id = company()?->id ?? user()?->company_id;

        return $id !== null ? (int) $id : null;
    }

    protected function warehouseFailResponse(Request $request, string $message, int $status = 422): JsonResponse|RedirectResponse
    {
        if ($request->ajax()) {
            return response()->json(Reply::error($message), $status);
        }

        return back()->with('error', $message)->withInput();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function handleWarehouseThrowable(Request $request, string $logLabel, \Throwable $e, array $payload = []): JsonResponse|RedirectResponse
    {
        if ($e instanceof ValidationException) {
            throw $e;
        }

        if ($e instanceof WarehouseBusinessException) {
            Log::warning($logLabel, array_merge($payload, $e->getLogContext(), [
                'user_message' => $e->getUserMessage(),
            ]));

            return $this->warehouseFailResponse($request, $e->getUserMessage());
        }

        Log::error($logLabel . ': ' . $e->getMessage(), array_merge($payload, [
            'exception_class' => get_class($e),
            'trace' => $e->getTraceAsString(),
        ]));

        return $this->warehouseFailResponse($request, __('warehouse::app.err_unexpected_try_again'));
    }
}
