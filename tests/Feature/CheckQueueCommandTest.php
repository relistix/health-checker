<?php

namespace Relistix\HealthChecker\Tests\Feature;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Relistix\HealthChecker\Jobs\QueueHealthPingJob;
use Relistix\HealthChecker\Tests\TestCase;

class CheckQueueCommandTest extends TestCase
{
    public function test_dispatches_job_onto_configured_queue(): void
    {
        config()->set('healthchecker.queue_checks.alpha', [
            'uuid' => 'alpha-uuid',
            'queue' => 'probes',
            'connection' => 'redis',
        ]);

        Queue::fake();

        $this->artisan('healthchecker:check-queue', ['check' => 'alpha'])
            ->assertExitCode(0);

        Queue::assertPushedOn('probes', QueueHealthPingJob::class, function ($job) {
            return $job->checkName === 'alpha'
                && $job->queue === 'probes'
                && $job->connection === 'redis';
        });
    }

    public function test_option_overrides_config_queue(): void
    {
        config()->set('healthchecker.queue_checks.alpha', [
            'uuid' => 'alpha-uuid',
            'queue' => 'probes',
        ]);

        Queue::fake();

        $this->artisan('healthchecker:check-queue', ['check' => 'alpha', '--queue' => 'override'])
            ->assertExitCode(0);

        Queue::assertPushedOn('override', QueueHealthPingJob::class);
    }

    public function test_missing_check_returns_failure_and_logs(): void
    {
        Log::spy();
        Queue::fake();

        $this->artisan('healthchecker:check-queue', ['check' => 'nope'])
            ->assertExitCode(1);

        Queue::assertNothingPushed();
        Log::shouldHaveReceived('error')->atLeast()->once();
    }

    public function test_disabled_short_circuits_without_dispatching(): void
    {
        config()->set('healthchecker.enabled', false);
        config()->set('healthchecker.queue_checks.alpha', [
            'uuid' => 'alpha-uuid',
            'queue' => 'probes',
        ]);

        Queue::fake();

        $this->artisan('healthchecker:check-queue', ['check' => 'alpha'])
            ->assertExitCode(0);

        Queue::assertNothingPushed();
    }

    public function test_missing_queue_name_returns_failure(): void
    {
        config()->set('healthchecker.queue_checks.broken', [
            'uuid' => 'x',
        ]);

        Log::spy();
        Queue::fake();

        $this->artisan('healthchecker:check-queue', ['check' => 'broken'])
            ->assertExitCode(1);

        Queue::assertNothingPushed();
        Log::shouldHaveReceived('error')->atLeast()->once();
    }
}
