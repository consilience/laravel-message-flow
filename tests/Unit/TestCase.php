<?php

namespace Consilience\Laravel\MessageFlow\Tests;

use Consilience\Laravel\MessageFlow\MessageFlowProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
  public function setUp(): void
  {
    parent::setUp();
    // additional setup
  }

  protected function getPackageProviders($app)
  {
    return [
        MessageFlowProvider::class,
    ];
  }

  protected function getEnvironmentSetUp($app)
  {
    // perform environment setup
  }
}
