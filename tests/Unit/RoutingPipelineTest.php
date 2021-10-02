<?php

namespace Consilience\Laravel\MessageFlow\Tests\Unit;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Consilience\Laravel\MessageFlow\Tests\TestCase;
use Consilience\Laravel\MessageFlow\Jobs\RoutingPipeline;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Consilience\Laravel\MessageFlow\Pipes\LogStartRoutingPipeline;
use Consilience\Laravel\MessageFlow\Pipes\LogFinishRoutingPipeline;

class RoutingPipelineTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function nothing_is_logged_for_empty_pipeline()
    {
        // Setup.

        // The orchestra workbench does not seem to support Log::fake(),
        // so we will just spy instead.

        Log::spy();

        Config::set('message-flow.out.routing-pipeline', []);

        $messageFlowOut = MessageFlowOut::create([
            'payload' => ['foo' => ['bar' => 'baz']],
        ]);

        // Execute.

        // Run the pipeline for this outbound message.

        app()->call([new RoutingPipeline($messageFlowOut), 'handle']);

        // Assert.

        Log::shouldNotHaveReceived('debug');
    }

    /** @test */
    public function log_start_end_pipeline()
    {
        Log::spy();

        Config::set('message-flow.out.routing-pipeline', [
            LogStartRoutingPipeline::class,
            LogFinishRoutingPipeline::class,
        ]);

        $messageFlowOut = MessageFlowOut::create([
            'payload' => ['foo' => ['bar' => 'baz']],
        ]);

        app()->call([new RoutingPipeline($messageFlowOut), 'handle']);

        // We aren't too bothered about the detail content of the messages,
        // just that adding two pipes have resulted in two debug messages
        // being logged.

        Log::shouldHaveReceived('debug')->times(2);
    }
}
