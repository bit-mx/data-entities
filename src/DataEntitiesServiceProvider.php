<?php

namespace BitMx\DataEntities;

use Illuminate\Support\ServiceProvider;

class DataEntitiesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    protected function registerCommands(): void
    {
        $this->commands([
            Commands\MakeDataEntity::class,
        ]);
    }
}
