<?php

namespace Relistix\HealthChecker\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Relistix\HealthChecker\Exceptions\HealthCheckerConfigurationException;
use Relistix\HealthChecker\Mail\HealthCheckMailable;
use Throwable;

class CheckMailCommand extends Command
{
    protected $signature = 'healthchecker:check-mail
                            {check=default : Name of the mail check (key in healthchecker.mail_checks)}
                            {--to= : Override the recipient email address from config}';

    protected $description = 'Send a probe email (typically to a Healthchecks.io email-ping address) to verify mail-server availability.';

    public function handle(): int
    {
        $name = (string) $this->argument('check');
        $cfg = config("healthchecker.mail_checks.{$name}");

        if (!is_array($cfg)) {
            $message = "Healthchecker mail check [{$name}] is not configured.";
            Log::error($message, ['check' => $name]);
            $this->error($message);
            return self::FAILURE;
        }

        $to = $this->option('to') ?: ($cfg['recipient'] ?? null);
        if (empty($to)) {
            $e = HealthCheckerConfigurationException::missingMailRecipient($name);
            Log::error($e->getMessage(), ['check' => $name]);
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $subject = $cfg['subject'] ?? 'Healthchecks.io mail probe';
        $mailer = $cfg['mailer'] ?? null;

        try {
            $mailerInstance = $mailer ? Mail::mailer($mailer) : Mail::mailer();
            $mailerInstance->to($to)->send(new HealthCheckMailable($name, $subject));
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
