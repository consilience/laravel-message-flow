<?php

namespace Consilience\Laravel\MessageFlow\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Consilience\Laravel\MessageFlow\Tests\TestCase;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;

class OutMessageScopesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function scope_to_send_returns_new_and_failed_messages()
    {
        // Create messages with different statuses
        $newMessage = MessageFlowOut::create([
            'name' => 'test',
            'status' => MessageFlowOut::STATUS_NEW,
        ]);

        $failedMessage = MessageFlowOut::create([
            'name' => 'test',
            'status' => MessageFlowOut::STATUS_FAILED,
        ]);

        $queuedMessage = MessageFlowOut::create([
            'name' => 'test',
            'status' => MessageFlowOut::STATUS_QUEUED,
        ]);

        $completeMessage = MessageFlowOut::create([
            'name' => 'test',
            'status' => MessageFlowOut::STATUS_COMPLETE,
        ]);

        // Test the toSend scope
        $messagesToSend = MessageFlowOut::toSend()->get();

        $this->assertCount(2, $messagesToSend);
        $this->assertTrue($messagesToSend->contains($newMessage));
        $this->assertTrue($messagesToSend->contains($failedMessage));
        $this->assertFalse($messagesToSend->contains($queuedMessage));
        $this->assertFalse($messagesToSend->contains($completeMessage));
    }

    /** @test */
    public function scope_is_queued_returns_only_queued_messages()
    {
        // Create messages with different statuses
        $queuedMessage1 = MessageFlowOut::create([
            'name' => 'test',
            'status' => MessageFlowOut::STATUS_QUEUED,
        ]);

        $queuedMessage2 = MessageFlowOut::create([
            'name' => 'test',
            'status' => MessageFlowOut::STATUS_QUEUED,
        ]);

        $newMessage = MessageFlowOut::create([
            'name' => 'test',
            'status' => MessageFlowOut::STATUS_NEW,
        ]);

        // Test the isQueued scope
        $queuedMessages = MessageFlowOut::isQueued()->get();

        $this->assertCount(2, $queuedMessages);
        $this->assertTrue($queuedMessages->contains($queuedMessage1));
        $this->assertTrue($queuedMessages->contains($queuedMessage2));
        $this->assertFalse($queuedMessages->contains($newMessage));
    }

    /** @test */
    public function is_sent_returns_true_for_queued_and_complete()
    {
        $queuedMessage = MessageFlowOut::create([
            'status' => MessageFlowOut::STATUS_QUEUED,
        ]);

        $completeMessage = MessageFlowOut::create([
            'status' => MessageFlowOut::STATUS_COMPLETE,
        ]);

        $newMessage = MessageFlowOut::create([
            'status' => MessageFlowOut::STATUS_NEW,
        ]);

        $failedMessage = MessageFlowOut::create([
            'status' => MessageFlowOut::STATUS_FAILED,
        ]);

        $this->assertTrue($queuedMessage->isSent());
        $this->assertTrue($completeMessage->isSent());
        $this->assertFalse($newMessage->isSent());
        $this->assertFalse($failedMessage->isSent());
    }

    /** @test */
    public function is_new_returns_true_for_new_messages()
    {
        $newMessage = MessageFlowOut::create([
            'status' => MessageFlowOut::STATUS_NEW,
        ]);

        $queuedMessage = MessageFlowOut::create([
            'status' => MessageFlowOut::STATUS_QUEUED,
        ]);

        $this->assertTrue($newMessage->isNew());
        $this->assertFalse($queuedMessage->isNew());
    }

    /** @test */
    public function get_json_payload_attribute_returns_json_string()
    {
        $payload = ['foo' => 'bar', 'nested' => ['key' => 'value']];

        $message = MessageFlowOut::create([
            'payload' => $payload,
        ]);

        $jsonPayload = $message->json_payload;

        $this->assertIsString($jsonPayload);
        $this->assertSame($payload, json_decode($jsonPayload, true));
    }

    /** @test */
    public function default_values_are_set_correctly()
    {
        $message = MessageFlowOut::create([]);

        $this->assertSame(MessageFlowOut::STATUS_NEW, $message->status);
        $this->assertSame(MessageFlowOut::DEFAULT_NAME, $message->name);
        $this->assertSame(json_decode(MessageFlowOut::DEFAULT_PAYLOAD, true), $message->payload);
    }
}
