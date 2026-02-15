<?php

namespace Consilience\Laravel\MessageFlow\Jobs;

/**
 * Accept an inbound message and store it for further handling
 * by the application.
 */

use Consilience\Laravel\MessageFlow\Models\MessageFlowIn;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ReceiveMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     * Note: all parameters are deserialized, but none from models.
     * This is necessary as the receiving application will not have
     * the models of the sending application.
     */
    public function __construct(
        protected string $jsonPayload,
        protected string $uuid,
        protected ?string $name = null,
    ) {}

    public function handle(): void
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
        ]);

        $messageCacheIn->jsonPayload = $this->jsonPayload;

        if ($this->name !== null) {
            $messageCacheIn->name = $this->name;
        }

        $messageCacheIn->save();
    }
}
