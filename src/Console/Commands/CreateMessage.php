<?php

namespace Consilience\Laravel\MessageFlow\Console\Commands;

use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Illuminate\Console\Command;
use Throwable;

class CreateMessage extends Command
{
    protected $signature = 'message-flow:create-message
        {--name=default : The name of the message, for routing}
        {--payload= : The JSON payload to send}
        {--status=new : The message initial status}
    ';

    protected $description = 'Create a hand-made outbound message';

    public function handle(): int
    {
        $name = $this->option('name');
        $payload = $this->option('payload') ?: MessageFlowOut::DEFAULT_PAYLOAD;
        $status = $this->option('status') ?: MessageFlowOut::STATUS_NEW;

        try {
            $payload = json_decode($payload, false, 512, JSON_THROW_ON_ERROR);
            
        } catch (Throwable $exception) {
            $this->error(sprintf(
                'Failed to parse JSON: %d %s',
                $exception->getCode(),
                $exception->getMessage()
            ));

            return 1;
        }

        $messageFlowOut = MessageFlowOut::create([
            'name' => $name,
            'payload' => $payload,
            'status' => $status,
        ]);

        $this->info('Dispatched outbound message:');
        $this->line($messageFlowOut->uuid);

        $this->info('Payload:');
        $this->line(json_encode($payload, JSON_PRETTY_PRINT));

        return 0;
    }
}
