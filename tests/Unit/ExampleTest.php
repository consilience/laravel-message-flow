<?php

namespace Consilience\Laravel\MessageFlow\Tests\Unit;

use Consilience\Laravel\MessageFlow\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_test_will_pass()
    {
        $this->assertTrue(true);
    }
}
