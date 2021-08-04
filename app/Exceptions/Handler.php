<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
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
        $this->reportable(function (HttpExceptionInterface $e, $request) {
            if (in_array($exception->getStatusCode(), [404, 500])) {
                Log::error($exception);
            }
        });

        $this->renderable(function (TokenMismatchException $e, $request) {
            return error('Invalid Token', 401);
        });

        $this->renderable(function (GuzzleClientException $e, $request) {
            $response = $e->getResponse();

            return response()->json(
                json_decode($response->getBody()), $res->getStatusCode()
            );
        });
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
                    ? response()->json(['status' => 'error', 'message' => 'Unauthorized', 'token' => csrf_token()], 401)
                    : redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'status' => 'fail',
            'data' => $exception->errors(),
        ], $exception->status);
    }
}
