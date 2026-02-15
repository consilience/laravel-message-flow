<?php

namespace Consilience\Laravel\MessageFlow\Jobs;

/**
 * Send the message through the routing pipeline.
 */

use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RoutingPipeline implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected MessageFlowOut $messageFlowOut,
    ) {}

    public function handle(): void
    {
        // The routing pipeline, for preparing each message before it
        // is ready to be sent to the queue.
        // Every pipe must save its changes if it has made any.

        $pipes = config('message-flow.out.routing-pipeline', []);

        app(Pipeline::class)
            ->send($this->messageFlowOut)
            ->through($pipes)
            ->then(function ($messageFlowOut) {
                if ($messageFlowOut->exists) {
                    // Save changes to the message only if a pipe has not deleted it.

                    $messageFlowOut->save();
                }
            });
    }
}
