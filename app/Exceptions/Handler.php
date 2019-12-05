<?php

namespace App\Exceptions;

use Exception;
use PDOException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'error' => 'Resource not found'
            ], 404);
        }
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'error' => 'API authorization failed'
            ], 401);
        }
        if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
            return response()->json([
                'error' => 'API token invalid'
            ], 401);
        }
        if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
            return response()->json([
                'error' => 'API token expired'
            ], 401);
        }
        if ($exception instanceof \Tymon\JWTAuth\Exceptions\UnauthorizedHttpException) {
            return response()->json([
                'error' => 'API token unauthorized'
            ], 401);
        }
        
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException) {
            switch (get_class($exception->getPrevious())) {
                case \Tymon\JWTAuth\Exceptions\TokenExpiredException::class:
                    return response()->json('Token has expired', $exception->getStatusCode());
                case \Tymon\JWTAuth\Exceptions\TokenInvalidException::class:
                    return response()->json('Token is invalid', $exception->getStatusCode());
                case \Tymon\JWTAuth\Exceptions\TokenBlacklistedException::class:
                    return response()->json('Token is blacklisted', $exception->getStatusCode());
                default:
                    break;
            }
        }
        
        if ($exception instanceof PDOException){
            Log::error('Database Error Exception: ' . $exception);
            return response()->json('Database request error', 500);
        }
        
        Log::error('Handler Error Exception: ' . $exception);
        return response()->json('Server Request Failed. Contact Administrator', 500);
        
        // return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
    }
}
