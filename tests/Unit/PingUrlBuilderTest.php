<?php

namespace Relistix\HealthChecker\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Relistix\HealthChecker\Support\PingUrlBuilder;

class PingUrlBuilderTest extends TestCase
{
    public function test_uuid_style_urls(): void
    {
        $b = new PingUrlBuilder('https://hc-ping.com', uuid: 'abc-123');

        $this->assertSame('https://hc-ping.com/abc-123', $b->success());
        $this->assertSame('https://hc-ping.com/abc-123/fail', $b->fail());
        $this->assertSame('https://hc-ping.com/abc-123/start', $b->start());
        $this->assertSame('https://hc-ping.com/abc-123/log', $b->log());
        $this->assertSame('https://hc-ping.com/abc-123/2', $b->exitStatus(2));
    }

    public function test_slug_style_urls(): void
    {
        $b = new PingUrlBuilder('https://hc-ping.com', uuid: null, pingKey: 'KEY', slug: 'my-job');

        $this->assertSame('https://hc-ping.com/KEY/my-job', $b->success());
        $this->assertSame('https://hc-ping.com/KEY/my-job/fail', $b->fail());
        $this->assertSame('https://hc-ping.com/KEY/my-job/start', $b->start());
        $this->assertSame('https://hc-ping.com/KEY/my-job/log', $b->log());
        $this->assertSame('https://hc-ping.com/KEY/my-job/0', $b->exitStatus(0));
    }

    public function test_trailing_slash_in_base_url_is_trimmed(): void
    {
        $b = new PingUrlBuilder('https://example.test/healthchecks/', uuid: 'u');
        $this->assertSame('https://example.test/healthchecks/u', $b->success());
    }

    public function test_uuid_takes_precedence_over_slug(): void
    {
        $b = new PingUrlBuilder('https://hc-ping.com', uuid: 'abc', pingKey: 'KEY', slug: 'my-job');
        $this->assertSame('https://hc-ping.com/abc', $b->success());
    }
}
