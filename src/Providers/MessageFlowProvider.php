<?php

namespace Consilience\Laravel\MessageFlow\Providers;

use Illuminate\Support\ServiceProvider;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Consilience\Laravel\MessageFlow\Observers\MessageFlowOutObserver;

class MessageFlowProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/message-flow.php', 'message-flow'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->publishes([
            __DIR__.'/../../config/message-flow.php' => config_path('message-flow.php')
        ], 'config');

        $this->publishes([
            __DIR__.'/../../database/migrations/' => database_path('migrations')
        ], 'migrations');

        MessageFlowOut::observe(MessageFlowOutObserver::class);
    }
}
