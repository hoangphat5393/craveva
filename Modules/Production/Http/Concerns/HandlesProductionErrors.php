<?php

namespace Modules\Production\Http\Concerns;

use App\Helper\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Modules\Warehouse\Exceptions\WarehouseBusinessException;

trait HandlesProductionErrors
{
    protected function productionFailResponse(Request $request, string $message, int $status = 422): JsonResponse|RedirectResponse
    {
        if ($request->ajax()) {
            return response()->json(Reply::error($message), $status);
        }

        return back()->with('error', $message)->withInput();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function handleProductionThrowable(Request $request, string $logLabel, \Throwable $e, array $payload = []): JsonResponse|RedirectResponse
    {
        if ($e instanceof ValidationException) {
            throw $e;
        }

        if ($e instanceof WarehouseBusinessException) {
            Log::warning($logLabel, array_merge($payload, $e->getLogContext(), [
                'user_message' => $e->getUserMessage(),
            ]));

            return $this->productionFailResponse($request, $e->getUserMessage());
        }

        if ($e instanceof InvalidArgumentException) {
            return $this->productionFailResponse($request, $e->getMessage());
        }

        Log::error($logLabel.': '.$e->getMessage(), array_merge($payload, [
            'exception_class' => get_class($e),
            'trace' => $e->getTraceAsString(),
        ]));

        return $this->productionFailResponse($request, __('production::app.unexpectedError'));
    }
}
