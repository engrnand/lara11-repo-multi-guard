<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

function exceptionErrors($exception)
{
    switch (class_basename($exception)) {
        case 'UnauthorizedException':
            $response = errorResponse('You do not have required authorization.', 403);
            break;

        case 'AuthenticationException':
            return errorResponse('You are not authenticated. Please login.', 401);

        case 'ValidationException':
            $response = errorResponse(array_values($exception->errors())[0] ?? '', 422, $exception->errors());
            break;

        case 'ModelNotFoundException':
            $modelBaseName = basename($exception->getModel());
            $ids  = $exception->getIds();
            $ids = implode(',', $ids);
            $response = errorResponse("No record found for model {$modelBaseName}"
                . ($ids != null ? " with ID {$ids}." : '.'), 404);
            break;

        case 'NotFoundHttpException':
            $response = errorResponse('The specified URL cannot be found.', 404);
            break;

        case 'AuthorizationException':
            $response = errorResponse($exception->getMessage(), 403);
            break;

        case 'MethodNotAllowedHttpException':
            $response = errorResponse($exception->getMessage(), 405);
            break;

        case 'ErrorException':
            $response = errorResponse($exception->getMessage(), $exception->getCode());
            break;
        case 'Error':

            $message = $exception->getMessage();
            $twillioFind = str_contains($message, "Unable to create record: Authenticate",);
            preg_match('/\b\d{3}\b/', $message, $matches);
            $statusCode = @$matches[0] ? 400 : $exception->getCode();
            $message = $twillioFind != '' ?
                "Unable to send message due to insufficient Twillio balance" : $message;
            $response = errorResponse($message,  $statusCode);
            break;


        default:
            $response = errorResponse($exception->getMessage(), $exception->getCode() ?? 500, config('app.debug') ?
                $exception->getTrace() : []);
            break;
    }


    return $response;
}

function addModuleRouteFiles()
{
    $fileSystem = new Filesystem;
    $SINGULAR_ROUTES = ['auth'];

    foreach ($fileSystem->directories(base_path('routes')) as $directory) {

        $directoryBaseName = basename($directory);
        $directoryUCF = Str::ucfirst($directoryBaseName);
        Route::group([
            "middleware" => ["api"],
            'namespace' => "App\Http\Controllers\Api\{$directoryUCF}",
        ], function () use ($SINGULAR_ROUTES, $fileSystem, $directoryBaseName) {

            $routeFiles = $fileSystem->files(base_path('routes' . DIRECTORY_SEPARATOR . $directoryBaseName));

            foreach ($routeFiles  as $routeFile) {
                $baseName = $routeFile->getBaseName();

                $parseName = pathinfo($baseName, PATHINFO_FILENAME);

                $pluralName = in_array(
                    $parseName,
                    $SINGULAR_ROUTES,
                ) ? $parseName : Str::plural($parseName);


                Route::as("api.{$directoryBaseName}.{$pluralName}.")
                    ->prefix("api/{$directoryBaseName}/{$pluralName}")
                    ->group(base_path("routes/$directoryBaseName/$baseName"));
            }
        });
    }
}
