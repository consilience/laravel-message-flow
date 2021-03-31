<?php

namespace Consilience\Laravel\MessageFlow\Contracts;

/**
 * Pipes stages for the message routing pipeline.
 */

use Closure;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;

interface RoutingPipe
{
  /**
   * Handle a stage [a pipe] in the pipeline.
   *
   * @param MessageFlowOut $content
   * @param Closure $next
   * @return void
   */
  public function handle(MessageFlowOut $content, Closure $next);
}
