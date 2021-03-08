<?php

namespace Consilience\Laravel\MessageFlow\Jobs;

/**
 * Send the message throuygh the routing pipeline.
 */

use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Consilience\Laravel\MessageFlow\Pipes\DispatchSendMessage;
use Consilience\Laravel\MessageFlow\Pipes\RouteFromConfig;
use Illuminate\Pipeline\Pipeline;

class RoutingPipeline implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Where teh prepared message will be stored ready to go.
     *
     * @var mMssageFlowOut
     */
    protected $messageFlowOut;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MessageFlowOut $messageFlowOut)
    {
        $this->messageFlowOut = $messageFlowOut;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // The routing pipeline, for preparing each message before it
        // is ready to be sent to the queue.
        // Every pipe must save its changes if it has made any.

        Log::info('Running routing pipeline');

        $pipes = config('message-flow.out.routing-pipeline', []);

        app(Pipeline::class)
            ->send($this->messageFlowOut)
            ->through($pipes)
            ->then(function ($messageFlowOut) {
                if ($messageFlowOut->exists) {
                    $messageFlowOut->save();
                }

                Log::debug('Routing pipeline complete for MessageFlowOut', [
                    'uuid' => $messageFlowOut->uuid,
                    'status' => $messageFlowOut->status,
                    'isDeleted' => ! $messageFlowOut->exists,
                ]);
            });
    }
}
