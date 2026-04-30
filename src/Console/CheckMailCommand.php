<?php

namespace Relistix\HealthChecker\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Relistix\HealthChecker\Console\Concerns\ReportsConfigurationFailure;
use Relistix\HealthChecker\Exceptions\HealthCheckerConfigurationException;
use Relistix\HealthChecker\Mail\HealthCheckMailable;
use Throwable;

class CheckMailCommand extends Command
{
    use ReportsConfigurationFailure;

    protected $signature = 'healthchecker:check-mail
                            {check=default : Name of the mail check (key in healthchecker.mail_checks)}
                            {--to= : Override the recipient email address from config}';

    protected $description = 'Send a probe email (typically to a Healthchecks.io email-ping address) to verify mail-server availability.';

    public function handle(): int
    {
        if (!config('healthchecker.enabled', true)) {
            $this->info('Healthchecker is disabled; skipping mail probe.');
            return self::SUCCESS;
        }

        $name = (string) $this->argument('check');
        $cfg = config("healthchecker.mail_checks.{$name}");

        if (!is_array($cfg)) {
            return $this->failWith(HealthCheckerConfigurationException::unknownCheck('mail', $name), $name);
        }

        $to = $this->option('to') ?: ($cfg['recipient'] ?? null);
        if (empty($to)) {
            return $this->failWith(HealthCheckerConfigurationException::missingMailRecipient($name), $name);
        }

        $subject = $cfg['subject'] ?? 'Healthchecks.io mail probe';
        $mailer = ($cfg['mailer'] ?? '') ?: null;

        try {
            Mail::mailer($mailer)->to($to)->send(new HealthCheckMailable($name, $subject));
        } catch (Throwable $e) {
            Log::error('Failed to send healthchecker mail probe', [
                'check' => $name,
                'recipient' => $to,
                'mailer' => $mailer,
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);
            $this->error("Failed to send mail probe: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->info("Sent mail health probe for [{$name}] to [{$to}].");
        return self::SUCCESS;
    }
}
