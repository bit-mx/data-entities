<?php

namespace BitMx\DataEntities;

use Illuminate\Support\ServiceProvider;

class DataEntitiesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/data-entities.php' => config_path('data-entities.php'),
        ], 'config');
    }

    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }

        $this->mergeConfigFrom(__DIR__.'/../config/data-entities.php', 'data-entities');
    }

    protected function registerCommands(): void
    {
        $this->commands([
            Commands\MakeDataEntity::class,
        ]);
    }
}
