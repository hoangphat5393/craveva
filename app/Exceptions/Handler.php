<?php

namespace App\Exceptions;

use Froiden\RestAPI\Exceptions\ApiException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        ApiException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {

        $this->renderable(function (ApiException $e, $request) {
            return response()->json($e, 403);
        });

        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (\Exception $e) {
            if ($e->getPrevious() instanceof TokenMismatchException) {
                return redirect()->route('login');
            }
        });

        $this->renderable(function (InvalidSignatureException $e) {
            return response()->view('errors.link-expired', [], 403);
        });
    }

    public function report(Throwable $exception)
    {
        // #region agent log
        @file_put_contents(
            base_path('debug-0fea0f.log'),
            json_encode([
                'sessionId' => '0fea0f',
                'runId' => 'initial',
                'hypothesisId' => 'H6',
                'location' => 'Handler.php:report',
                'message' => 'Exception reported by Laravel handler',
                'data' => [
                    'exceptionClass' => get_class($exception),
                    'exceptionMessage' => (string) $exception->getMessage(),
                    'exceptionFile' => (string) $exception->getFile(),
                    'exceptionLine' => (int) $exception->getLine(),
                    'traceTop' => array_slice(explode("\n", (string) $exception->getTraceAsString()), 0, 4),
                    'requestPath' => request()?->path(),
                    'requestMethod' => request()?->method(),
                ],
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_UNICODE).PHP_EOL,
            FILE_APPEND
        );
        // #endregion

        if (app()->bound('sentry') && $this->shouldReport($exception) && config('services.sentry.enabled')) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'message' => __('validation.givenDataInvalid'),
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TokenMismatchException) {

            return redirect(route('login'))->with('message', 'You page session expired. Please try again');
        }

        return parent::render($request, $exception);
    }
}
