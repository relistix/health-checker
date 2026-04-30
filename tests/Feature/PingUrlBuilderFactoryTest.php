<?php

namespace Relistix\HealthChecker\Tests\Feature;

use Relistix\HealthChecker\Exceptions\HealthCheckerConfigurationException;
use Relistix\HealthChecker\Support\PingUrlBuilderFactory;
use Relistix\HealthChecker\Tests\TestCase;

class PingUrlBuilderFactoryTest extends TestCase
{
    public function test_resolves_uuid_check_for_queue(): void
    {
        config()->set('healthchecker.queue_checks.foo', [
            'uuid' => 'aaa-bbb',
            'queue' => 'foo',
        ]);

        $url = $this->app->make(PingUrlBuilderFactory::class)
            ->forQueueCheck('foo')
            ->success();

        $this->assertSame('https://hc-ping.com/aaa-bbb', $url);
    }

    public function test_falls_back_to_top_level_ping_key_for_slug_check(): void
    {
        config()->set('healthchecker.ping_key', 'GLOBAL-KEY');
        config()->set('healthchecker.mail_checks.bar', [
            'slug' => 'mail-bar',
            'recipient' => 'x@example.com',
        ]);

        $url = $this->app->make(PingUrlBuilderFactory::class)
            ->forMailCheck('bar')
            ->fail();

        $this->assertSame('https://hc-ping.com/GLOBAL-KEY/mail-bar/fail', $url);
    }

    public function test_unknown_check_throws(): void
    {
        $this->expectException(HealthCheckerConfigurationException::class);
        $this->expectExceptionMessage('queue check [missing] is not configured');

        $this->app->make(PingUrlBuilderFactory::class)->forQueueCheck('missing');
    }

    public function test_missing_ping_target_throws(): void
    {
        config()->set('healthchecker.queue_checks.broken', [
            'queue' => 'broken',
        ]);

        $this->expectException(HealthCheckerConfigurationException::class);
        $this->expectExceptionMessage('missing a ping target');

        $this->app->make(PingUrlBuilderFactory::class)->forQueueCheck('broken');
    }
}
