<?php

namespace Consilience\Laravel\MessageFlow\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Consilience\Laravel\MessageFlow\Tests\TestCase;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Illuminate\Support\Str;

class OutMessageCreateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function an_out_message_gets_a_uuid()
    {
        $messageFlowOut = MessageFlowOut::create([
            'payload' => ['foo' => 'bar'],
        ]);

        $this->assertTrue(Str::isUuid($messageFlowOut->uuid));

        $messageFlowOut->refresh();

        $this->assertSame(['foo' => 'bar'], $messageFlowOut->payload);
    }
}
