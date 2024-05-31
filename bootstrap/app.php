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
        //
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
