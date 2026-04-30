<?php

namespace Relistix\HealthChecker\Tests\Feature;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Relistix\HealthChecker\Support\Pinger;
use Relistix\HealthChecker\Tests\TestCase;

class PingerTest extends TestCase
{
    public function test_successful_ping_returns_true_and_posts_body(): void
    {
        Http::fake([
            'hc-ping.com/*' => Http::response('OK', 200),
        ]);

        $result = $this->app->make(Pinger::class)->ping('https://hc-ping.com/abc', [
            'check' => 'default',
        ]);

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://hc-ping.com/abc'
                && $request->method() === 'POST'
                && $request['check'] === 'default';
        });
    }

    public function test_non_success_logs_warning_and_returns_false(): void
    {
        Http::fake([
            'hc-ping.com/*' => Http::response('Not found', 404),
        ]);

        Log::spy();

        $result = $this->app->make(Pinger::class)->ping('https://hc-ping.com/abc');

        $this->assertFalse($result);
        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn ($message) => str_contains($message, 'non-success'));
    }

    public function test_exception_is_caught_and_logged(): void
    {
        Http::fake(function () {
            throw new ConnectionException('boom');
        });

        Log::spy();

        $result = $this->app->make(Pinger::class)->ping('https://hc-ping.com/abc');

        $this->assertFalse($result);
        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn ($message) => str_contains($message, 'failed'));
    }
}
