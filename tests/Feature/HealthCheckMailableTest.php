<?php

namespace Relistix\HealthChecker\Tests\Feature;

use Relistix\HealthChecker\Mail\HealthCheckMailable;
use Relistix\HealthChecker\Tests\TestCase;

class HealthCheckMailableTest extends TestCase
{
    public function test_renders_probe_view(): void
    {
        $this->assertStringContainsString('Check:', (new HealthCheckMailable('alpha', 's'))->render());
    }
}
