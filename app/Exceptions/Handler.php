<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Inertia\Inertia;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Prepare exception for rendering.
     *
     * @return \Throwable
     */
    public function render($request, Throwable $e)
    {
        /** @var \Symfony\Component\HttpFoundation\Response */
        $response = parent::render($request, $e);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ], $response->status());
        }

        if (! app()->environment(['local', 'testing']) && in_array($response->status(), [500, 503, 404])) {
            return Inertia::render('Error', ['status' => $response->status()])
                ->toResponse($request)
                ->setStatusCode($response->status());
        } elseif ($response->status() === 419) {
            return back()->error('The page has expired', 'Please refresh your page and try again.');
        } elseif ($response->status() === 403) {
            return back()->error('Unauthorized', 'You do not have the necessary permissions to perform this action.');
        }

        return $response;
    }
}
