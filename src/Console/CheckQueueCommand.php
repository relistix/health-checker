<?php

namespace Relistix\HealthChecker\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Relistix\HealthChecker\Console\Concerns\ReportsConfigurationFailure;
use Relistix\HealthChecker\Exceptions\HealthCheckerConfigurationException;
use Relistix\HealthChecker\Jobs\QueueHealthPingJob;
use Throwable;

class CheckQueueCommand extends Command
{
    use ReportsConfigurationFailure;

    protected $signature = 'healthchecker:check-queue
                            {check=default : Name of the queue check (key in healthchecker.queue_checks)}
                            {--queue= : Override the queue name from config}
                            {--connection= : Override the queue connection from config}';

    protected $description = 'Dispatch a queue health probe job; pinging Healthchecks.io once a worker processes it.';

    public function handle(): int
    {
        if (!config('healthchecker.enabled', true)) {
            $this->info('Healthchecker is disabled; skipping queue probe.');
            return self::SUCCESS;
        }

        $name = (string) $this->argument('check');
        $cfg = config("healthchecker.queue_checks.{$name}");

        if (!is_array($cfg)) {
            return $this->failWith(HealthCheckerConfigurationException::unknownCheck('queue', $name), $name);
        }

        $queue = $this->option('queue') ?: ($cfg['queue'] ?? null);
        if (empty($queue)) {
            return $this->failWith(HealthCheckerConfigurationException::missingQueueName($name), $name);
        }

        $connection = $this->option('connection') ?: ($cfg['connection'] ?? null);

        try {
            $pending = QueueHealthPingJob::dispatch($name)->onQueue($queue);
            if (!empty($connection)) {
                $pending->onConnection($connection);
            }
        } catch (Throwable $e) {
            Log::error('Failed to dispatch healthchecker queue probe', [
                'check' => $name,
                'queue' => $queue,
                'connection' => $connection,
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);
            $this->error("Failed to dispatch queue probe: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->info("Dispatched queue health probe for [{$name}] onto queue [{$queue}].");
        return self::SUCCESS;
    }
}
