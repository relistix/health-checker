<?php

namespace Relistix\HealthChecker\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Relistix\HealthChecker\Jobs\QueueHealthPingJob;
use Relistix\HealthChecker\Tests\TestCase;

class QueueHealthPingJobTest extends TestCase
{
    public function test_job_pings_configured_url_on_handle(): void
    {
        config()->set('healthchecker.queue_checks.alpha', [
            'uuid' => 'alpha-uuid',
            'queue' => 'alpha',
        ]);

        Http::fake([
            'hc-ping.com/*' => Http::response('OK', 200),
        ]);

        $job = new QueueHealthPingJob('alpha');
        $job->handle(
            $this->app->make(\Relistix\HealthChecker\Support\PingUrlBuilderFactory::class),
            $this->app->make(\Relistix\HealthChecker\Support\Pinger::class),
        );

        Http::assertSent(fn ($request) => $request->url() === 'https://hc-ping.com/alpha-uuid');
    }

    public function test_job_logs_and_swallows_when_check_missing(): void
    {
        Http::fake();
        Log::spy();

        $job = new QueueHealthPingJob('does-not-exist');
        $job->handle(
            $this->app->make(\Relistix\HealthChecker\Support\PingUrlBuilderFactory::class),
            $this->app->make(\Relistix\HealthChecker\Support\Pinger::class),
        );

        Http::assertNothingSent();
        Log::shouldHaveReceived('error')->atLeast()->once();
    }
}
