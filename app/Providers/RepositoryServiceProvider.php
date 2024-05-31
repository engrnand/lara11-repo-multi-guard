<?php

namespace App\Providers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $repositoryNamespace = 'App\Repositories\Eloquents';
        $contractsNamespace = 'App\Repositories\Contracts';

        $files = (new Filesystem)->files(app_path('Repositories/Eloquents'));

        foreach ($files as $file) {
            $className = $file->getBasename('.php');

            $repositoryClass = "{$repositoryNamespace}\\{$className}";
            $contractInterface = "{$contractsNamespace}\\{$className}Contract";

            if (class_exists($repositoryClass) && interface_exists($contractInterface)) {
                $this->app->bind($contractInterface, $repositoryClass);
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
