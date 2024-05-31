<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
            then:function(){
                addModuleRouteFiles();
            }
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
    switch (class_basename($exceptions)) {
        case 'UnauthorizedException':
            $response = errorResponse('You do not have required authorization.', 403);
            break;

        case 'AuthenticationException':
            return errorResponse('You are not authenticated. Please login.', 401);

        case 'ValidationException':
            $response = errorResponse(array_values($exceptions->errors())[0] ?? '', 422, $exceptions->errors());
            break;

        case 'ModelNotFoundException':
            $modelBaseName = basename($exceptions->getModel());
            $ids  = $exceptions->getIds();
            $ids = implode(',', $ids);
            $response = errorResponse("No record found for model {$modelBaseName}"
            . ($ids != null ? " with ID {$ids}." : '.'), 404);
            break;

        case 'NotFoundHttpException':
            $response = errorResponse('The specified URL cannot be found.', 404);
            break;

        case 'AuthorizationException':
            $response = errorResponse($exceptions->getMessage(), 403);
            break;

        case 'MethodNotAllowedHttpException':
            $response = errorResponse($exceptions->getMessage(), 405);
            break;

        case 'ErrorException':
            $response = errorResponse($exceptions->getMessage(), $exceptions->getCode());
            break;
        case 'Error':

            $message = $exceptions->getMessage();
            $twillioFind = str_contains($message, "Unable to create record: Authenticate",);
            preg_match('/\b\d{3}\b/', $message, $matches);
            $statusCode = @$matches[0] ? 400 : $exceptions->getCode();
            $message = $twillioFind != '' ?
                "Unable to send message due to insufficient Twillio balance" : $message;
            $response = errorResponse($message,  $statusCode);
            break;


        default:
            $response = errorResponse($exceptions->getMessage(), $exceptions->getCode() ?? 500, config('app.debug') ?
                $exceptions->getTrace() : []);
            break;
    }


    return $response;
    })->create();

    function addModuleRouteFiles(){
      $singularRoutesPrefix = ['auth'];
        Route::group([
            "middleware" => ["api"],
            'namespace' => 'App\Http\Controllers\Api',
        ], function () use($singularRoutesPrefix){

            $routeFiles = (new Filesystem)->files(base_path('routes' . DIRECTORY_SEPARATOR . 'api-modules'));

            foreach ($routeFiles  as $routeFile) {
                $baseName = $routeFile->getBaseName();

                $parseName = pathinfo($baseName, PATHINFO_FILENAME);

                $pluralName = in_array(
                    $parseName,
                    $singularRoutesPrefix,
                ) ? $parseName: Str::plural($parseName);


                Route::as("api.{$pluralName}.")
                    ->prefix("api/{$pluralName}")
                    ->group(base_path('routes/api-modules/' . $baseName));
            }
        });
    }
