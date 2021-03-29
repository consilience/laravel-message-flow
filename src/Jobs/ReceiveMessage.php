<?php

namespace Consilience\Laravel\MessageFlow\Jobs;

/**
 * Accept an inbound message and store it for further handling
 * by the application.
 */

use Consilience\Laravel\MessageFlow\Models\MessageFlowIn;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReceiveMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The structured message that has been sent, as a JSON string.
     *
     * @var string
     */
    protected $jsonPayload;

    /**
     * Matches the UUID on the sending application.
     *
     * @var string
     */
    protected $uuid;

    /**
     * Name to route the message as needed.
     *
     * @var string
     */
    protected $name;

    /**
     * Create a new job instance.
     * Note: all parameters are deserialised, but none from models.
     * This is necessary as the receiving application will not have
     * the models of the sending application.
     *
     * @param string $payload JSON format
     * @param string $uuid
     * @param string|null $name
     *
     * @return void
     */
    public function __construct(string $jsonPayload, string $uuid, ?string $name = null)
    {
        $this->jsonPayload = $jsonPayload;
        $this->uuid = $uuid;
        $this->name = $name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Store the payload through the model.

        if (MessageFlowIn::where('uuid', '=', $this->uuid)->exists()) {
            // Might want to compare hashes of the payload to confirm it
            // really is the same message.

            Log::warning('Received duplicate message; ignoring', [
                'uuid' => $this->uuid,
                'name' => $this->name,
            ]);

            return;
        }

        $messageCacheIn = new MessageFlowIn([
            'uuid' => $this->uuid,
            //'jsonPayload' => $this->jsonPayload,
        ]);

        $messageCacheIn->jsonPayload = $this->jsonPayload;

        if ($this->name !== null) {
            $messageCacheIn->name = $this->name;
        }

        $messageCacheIn->save();
    }
}
