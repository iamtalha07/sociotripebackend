<?php

use Illuminate\Support\Facades\DB;
use App\Http\Responses\BaseResponse;
use Illuminate\Foundation\Application;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (Throwable $e) {
            if (request()->is('api/*') && ! ($e instanceof ValidationException)) {
                $httpCode = $statusCode = $msg = '';

                $httpCode = $statusCode = match (class_basename($e)) {
                    'NotFoundHttpException' => 404,
                    'MethodNotAllowedHttpException' => 405,
                    'AuthenticationException', 'AuthorizationException' => 401,
                    'QueryException' => 500,
                    'HttpResponseException' => 403,
                    default => 500
                };

                $msg = match (class_basename($e)) {
                    'NotFoundHttpException' => ($e->getMessage() == '') ? 'Invalid Route Requested' : 'Requested resource not found',
                    'MethodNotAllowedHttpException' => 'Invalid method Used',
                    'AuthenticationException' => 'Invalid or Expired Token',
                    'AuthorizationException' => 'Access Denied',
                    'QueryException' => 'SQL Error',
                    'HttpResponseException' => 'You are not authorized to perform this operation',
                    default => 'Internal Server Error',
                };

                DB::rollback();

                return new BaseResponse($httpCode, $statusCode, $e->getMessage() ?? $msg, []);
            }

            if (request()->is('api/*') && $e instanceof ValidationException) {
                DB::rollback();

                return new BaseResponse(
                    STATUS_CODE_BADREQUEST,
                    STATUS_CODE_BADREQUEST,
                    $e->validator->errors()->first()
                );
            }
        });
    })->create();
