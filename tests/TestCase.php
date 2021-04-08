<?php

namespace Consilience\Laravel\MessageFlow\Tests;

use Consilience\Laravel\MessageFlow\MessageFlowServiceProvider;

/** @package Consilience\Laravel\MessageFlow\Tests */
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
      MessageFlowServiceProvider::class,
    ];
  }

  protected function getEnvironmentSetUp($app)
  {
    // perform environment setup
  }
}
