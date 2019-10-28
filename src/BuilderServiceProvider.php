<?php

namespace DucCnzj\EsBuilder;

use Illuminate\Support\ServiceProvider;
use DucCnzj\EsBuilder\Contracts\BuilderInterface;

class BuilderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(BuilderInterface::class, function ($app, $model) {
            return $app->make(Builder::class, $model);
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/es.php' => $this->app['path.config'].DIRECTORY_SEPARATOR.'es.php',
            ]);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
