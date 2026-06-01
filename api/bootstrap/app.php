<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

use App\Http\Middleware\ForceJsonResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (ValidationException $e, $request) {
            return response()->json([
                'status' => 'error',
                'code' => 'VALIDATION_ERROR',
                'message' => 'Erro de validação',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            return response()->json([
                'status' => 'error',
                'code' => 'UNAUTHENTICATED',
                'message' => 'Não autenticado',
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $e, $request) {
            return response()->json([
                'status' => 'error',
                'code' => 'FORBIDDEN',
                'message' => 'Acesso negado',
            ], 403);
        });

        $exceptions->render(function (ModelNotFoundException $e, $request) {
            return response()->json([
                'status' => 'error',
                'code' => 'NOT_FOUND',
                'message' => 'Recurso não encontrado',
            ], 404);
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'status' => 'error',
                'code' => 'ROUTE_NOT_FOUND',
                'message' => 'Rota não encontrada',
            ], 404);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            return response()->json([
                'status' => 'error',
                'code' => 'METHOD_NOT_ALLOWED',
                'message' => 'Método não permitido',
            ], 405);
        });

        $exceptions->render(function (HttpException $e, $request) {
            return response()->json([
                'status' => 'error',
                'code' => 'HTTP_ERROR',
                'message' => $e->getMessage() ?: 'Erro HTTP',
            ], $e->getStatusCode());
        });

        // fallback
        $exceptions->render(function (Throwable $e, $request) {
            return response()->json([
                'status' => 'error',
                'code' => 'SERVER_ERROR',
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'Erro interno do servidor',
            ], 500);
        });

    })->create();
