<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
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
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable([$this, 'renderApiValidationErrorsIfNeeded']);

        $this->renderable([$this, 'renderDefaultErrorPage']);
    }

    public function renderDefaultErrorPage(Throwable $th, $request)
    {
        if ($request->is('api/*')) {
            return;
        }

        if (!$th instanceof HttpExceptionInterface) {
            return;
        }

        /** @var \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface */
        $e = $th;

        return response()->view(
            'errors.default',
            [
                'exception' => $e
            ],
            $e->getStatusCode()
        );
    }

    public function renderApiValidationErrorsIfNeeded(Throwable $e, $request)
    {
        if (!$e instanceof ValidationException) {
            return;
        }

        if ($request->is('api/*')) {
            return response()->json([
                'validationErrors' => $e->errors()
            ], 422);
        }
    }
}
