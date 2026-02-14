<?php

namespace Consilience\Laravel\MessageFlow\Tests;

use Consilience\Laravel\MessageFlow\MessageFlowServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            MessageFlowServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // perform environment setup
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();
    }
}
