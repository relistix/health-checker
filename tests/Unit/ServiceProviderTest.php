<?php

namespace Relistix\HealthChecker\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Relistix\HealthChecker\Support\Pinger;
use Relistix\HealthChecker\Support\PingUrlBuilderFactory;
use Relistix\HealthChecker\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_config_is_merged(): void
    {
        $this->assertSame('https://hc-ping.com', config('healthchecker.base_url'));
        $this->assertIsArray(config('healthchecker.queue_checks'));
        $this->assertIsArray(config('healthchecker.mail_checks'));
    }

    public function test_singletons_are_bound(): void
    {
        $this->assertSame(
            $this->app->make(PingUrlBuilderFactory::class),
            $this->app->make(PingUrlBuilderFactory::class),
        );
        $this->assertSame(
            $this->app->make(Pinger::class),
            $this->app->make(Pinger::class),
        );
    }

    public function test_artisan_commands_are_registered(): void
    {
        $commands = array_keys(Artisan::all());
        $this->assertContains('healthchecker:check-queue', $commands);
        $this->assertContains('healthchecker:check-mail', $commands);
    }
}
