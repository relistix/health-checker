<?php

namespace Relistix\HealthChecker\Tests\Feature;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Relistix\HealthChecker\Mail\HealthCheckMailable;
use Relistix\HealthChecker\Tests\TestCase;

class CheckMailCommandTest extends TestCase
{
    public function test_sends_mailable_to_configured_recipient(): void
    {
        config()->set('healthchecker.mail_checks.alpha', [
            'recipient' => 'probe@example.com',
            'subject' => 'Probe subject',
        ]);

        Mail::fake();

        $this->artisan('healthchecker:check-mail', ['check' => 'alpha'])
            ->assertExitCode(0);

        Mail::assertSent(HealthCheckMailable::class, function ($mail) {
            return $mail->hasTo('probe@example.com')
                && $mail->checkName === 'alpha'
                && $mail->subjectLine === 'Probe subject';
        });
    }

    public function test_to_option_overrides_config_recipient(): void
    {
        config()->set('healthchecker.mail_checks.alpha', [
            'recipient' => 'default@example.com',
        ]);

        Mail::fake();

        $this->artisan('healthchecker:check-mail', ['check' => 'alpha', '--to' => 'override@example.com'])
            ->assertExitCode(0);

        Mail::assertSent(HealthCheckMailable::class, fn ($mail) => $mail->hasTo('override@example.com'));
    }

    public function test_missing_check_returns_failure_and_logs(): void
    {
        Log::spy();
        Mail::fake();

        $this->artisan('healthchecker:check-mail', ['check' => 'nope'])
            ->assertExitCode(1);

        Mail::assertNothingSent();
        Log::shouldHaveReceived('error')->atLeast()->once();
    }

    public function test_disabled_short_circuits_without_sending(): void
    {
        config()->set('healthchecker.enabled', false);
        config()->set('healthchecker.mail_checks.alpha', [
            'recipient' => 'probe@example.com',
        ]);

        Mail::fake();

        $this->artisan('healthchecker:check-mail', ['check' => 'alpha'])
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_missing_recipient_returns_failure(): void
    {
        config()->set('healthchecker.mail_checks.broken', []);

        Log::spy();
        Mail::fake();

        $this->artisan('healthchecker:check-mail', ['check' => 'broken'])
            ->assertExitCode(1);

        Mail::assertNothingSent();
        Log::shouldHaveReceived('error')->atLeast()->once();
    }
}
