<?php

namespace Consilience\Laravel\MessageFlow\Tests\Unit;

use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Consilience\Laravel\MessageFlow\Tests\TestCase;
use Consilience\Laravel\MessageFlow\Models\MessageFlowIn;

class InMessageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_in_message_can_be_created_with_uuid()
    {
        $uuid = Str::uuid()->toString();

        $messageFlowIn = MessageFlowIn::create([
            'uuid' => $uuid,
            'payload' => ['data' => 'test'],
        ]);

        $this->assertSame($uuid, $messageFlowIn->uuid);
        $this->assertSame(MessageFlowIn::STATUS_NEW, $messageFlowIn->status);
    }

    /** @test */
    public function an_in_message_stores_payload_correctly()
    {
        $uuid = Str::uuid()->toString();
        $payload = ['foo' => ['bar' => 'baz'], 'key' => 'value'];

        $messageFlowIn = MessageFlowIn::create([
            'uuid' => $uuid,
            'payload' => $payload,
        ]);

        $messageFlowIn->refresh();

        $this->assertSame($payload, $messageFlowIn->payload);
    }

    /** @test */
    public function an_in_message_can_be_marked_as_complete()
    {
        $uuid = Str::uuid()->toString();

        $messageFlowIn = MessageFlowIn::create([
            'uuid' => $uuid,
            'payload' => ['data' => 'test'],
        ]);

        $this->assertTrue($messageFlowIn->isNew());

        $messageFlowIn->setComplete()->save();

        $this->assertFalse($messageFlowIn->isNew());
        $this->assertSame(MessageFlowIn::STATUS_COMPLETE, $messageFlowIn->status);
    }

    /** @test */
    public function an_in_message_can_be_marked_as_failed()
    {
        $uuid = Str::uuid()->toString();

        $messageFlowIn = MessageFlowIn::create([
            'uuid' => $uuid,
            'payload' => ['data' => 'test'],
        ]);

        $this->assertTrue($messageFlowIn->isNew());

        $messageFlowIn->setFailed()->save();

        $this->assertFalse($messageFlowIn->isNew());
        $this->assertSame(MessageFlowIn::STATUS_FAILED, $messageFlowIn->status);
    }

    /** @test */
    public function an_in_message_can_retrieve_payload_path()
    {
        $uuid = Str::uuid()->toString();
        $payload = [
            'user' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
            'status' => 'active',
        ];

        $messageFlowIn = MessageFlowIn::create([
            'uuid' => $uuid,
            'payload' => $payload,
        ]);

        $this->assertSame('John Doe', $messageFlowIn->getPayloadPath('user.name'));
        $this->assertSame('john@example.com', $messageFlowIn->getPayloadPath('user.email'));
        $this->assertSame('active', $messageFlowIn->getPayloadPath('status'));
        $this->assertNull($messageFlowIn->getPayloadPath('nonexistent'));
        $this->assertSame('default', $messageFlowIn->getPayloadPath('nonexistent', 'default'));
    }

    /** @test */
    public function an_in_message_can_set_json_payload()
    {
        $uuid = Str::uuid()->toString();
        $jsonPayload = '{"key":"value","nested":{"data":"test"}}';

        $messageFlowIn = new MessageFlowIn();
        $messageFlowIn->uuid = $uuid;
        $messageFlowIn->json_payload = $jsonPayload;
        $messageFlowIn->save();

        $messageFlowIn->refresh();

        $this->assertSame(['key' => 'value', 'nested' => ['data' => 'test']], $messageFlowIn->payload);
    }
}
