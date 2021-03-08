<?php

namespace Consilience\Laravel\MessageFlow\Contracts;

/**
 * Pipes stages for the message routing pipeline.
 */

use Closure;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;

interface RoutingPipe
{
  public function handle(MessageFlowOut $content, Closure $next);
}
