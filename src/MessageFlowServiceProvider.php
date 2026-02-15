<?php

namespace Consilience\Laravel\MessageFlow;

use Consilience\Laravel\MessageFlow\Console\Commands\CreateMessage;
use Consilience\Laravel\MessageFlow\Console\Commands\ListMessages;
use Consilience\Laravel\MessageFlow\Console\Commands\PurgeMessages;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Consilience\Laravel\MessageFlow\Observers\NewOutboundObserver;
use Illuminate\Support\ServiceProvider;

class MessageFlowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/message-flow.php', 'message-flow'
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../config/message-flow.php' => config_path('message-flow.php')
        ], 'config');

        // This observer catches messages written to the outbound cache table so they
        // can be moved to the queue.

        MessageFlowOut::observe(NewOutboundObserver::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateMessage::class,
                ListMessages::class,
                PurgeMessages::class,
            ]);
        }
    }
}