<?php

namespace Consilience\Laravel\MessageFlow\Tests\Unit;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Consilience\Laravel\MessageFlow\Tests\TestCase;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Consilience\Laravel\MessageFlow\Pipes\RouteFromConfig;

class OutMessageCreateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_out_message_gets_a_uuid()
    {
        // No outbound pipeline, so the message goes nowhere.

        Config::set('message-flow.out.routing-pipeline', []);

        $messageFlowOut = MessageFlowOut::create([
            'payload' => ['foo' => ['bar' => 'baz']],
        ]);

        $this->assertTrue(Str::isUuid($messageFlowOut->uuid));

        $this->assertSame(MessageFlowOut::STATUS_NEW, $messageFlowOut->status);

        $messageFlowOut->refresh();

        // Confirm we get the payload back from storage in the same format.

        $this->assertSame(['foo' => ['bar' => 'baz']], $messageFlowOut->payload);

        // Still new.

        $this->assertSame(MessageFlowOut::STATUS_NEW, $messageFlowOut->status);

        // No routing, i.e. no queue chosen for this message.

        $this->assertNull($messageFlowOut->queue_connection);
        $this->assertNull($messageFlowOut->queue_name);
    }

    /** @test */
    public function an_out_message_routes_from_config()
    {
        Config::set('message-flow.out.routing-pipeline', [RouteFromConfig::class]);

        // The config mapping for "foo-test".

        Config::set('message-flow.out.name-mappings.foo-test', [
            'queue-connection' => 'redis-test',
            'queue-name' => 'distributed:queue-test',
        ]);

        // The message "name" selects the mapping.

        $messageFlowOut = MessageFlowOut::create([
            'name' => 'foo-test',
        ]);

        $messageFlowOut->refresh();

        $this->assertSame('redis-test', $messageFlowOut->queue_connection);
        $this->assertSame('distributed:queue-test', $messageFlowOut->queue_name);

        // And a non-mapped message.

        $messageFlowOut = MessageFlowOut::create([
            'name' => 'foo-bar',
        ]);

        $messageFlowOut->refresh();

        $this->assertNull($messageFlowOut->queue_connection);
        $this->assertNull($messageFlowOut->queue_name);

        // TODO: test default mapping, fallback mapping when set, and system mapping when no fallback.
    }
}
