<?php

namespace Consilience\Laravel\MessageFlow;

use Consilience\Laravel\MessageFlow\Console\Commands\CreateMessage;
use Consilience\Laravel\MessageFlow\Console\Commands\ListMessages;
use Illuminate\Support\ServiceProvider;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Consilience\Laravel\MessageFlow\Observers\MessageFlowOutObserver;
use CreateMessageFlowInTable;
use Throwable;

class MessageFlowServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/message-flow.php', 'message-flow'
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

        if ($this->app->runningInConsole()) {
            // Laravel does not autoload migration classes, so we must
            // do so here to check if they exist.

            collect([
                'migrations/*_create_message_flow_in_table.php',
                'migrations/*_create_message_flow_out_table.php',
            ])
            ->map(function ($item) {
                return glob(database_path($item));
            })
            ->flatten()
            ->each(function ($item) {
                include_once $item;
            });

            // Publish the migrations.

            if (! class_exists(CreateMessageFlowInTable::class)) {
                $timestamp = date('Y_m_d_His', time());

                $this->publishes([
                    __DIR__ . '/../database/migrations/create_message_flow_in_table.php.stub' => database_path('migrations/' . $timestamp . '_create_message_flow_in_table.php'),
                    __DIR__ . '/../database/migrations/create_message_flow_out_table.php.stub' => database_path('migrations/' . $timestamp . '_create_message_flow_out_table.php'),
                ], 'migrations');
            }
        }

        $this->publishes([
            __DIR__.'/../config/message-flow.php' => config_path('message-flow.php')
        ], 'config');

        // $this->publishes([
        //     __DIR__.'/../database/migrations/' => database_path('migrations')
        // ], 'migrations');

        // Observer catches messages written to the outbound cache table so they
        // can be moved to the queue.

        MessageFlowOut::observe(MessageFlowOutObserver::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateMessage::class,
                ListMessages::class,
            ]);
        }
    }
}
