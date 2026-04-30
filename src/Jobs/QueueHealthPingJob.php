<?php

namespace Relistix\HealthChecker\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Relistix\HealthChecker\Exceptions\HealthCheckerConfigurationException;
use Relistix\HealthChecker\Support\Pinger;
use Relistix\HealthChecker\Support\PingUrlBuilderFactory;
use Throwable;

class QueueHealthPingJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public string $checkName = 'default')
    {
    }

    public function handle(PingUrlBuilderFactory $factory, Pinger $pinger): void
    {
        try {
            $url = $factory->forQueueCheck($this->checkName)->success();
        } catch (HealthCheckerConfigurationException $e) {
            Log::error('Healthchecker queue probe job aborted (configuration error)', [
                'check' => $this->checkName,
                'error' => $e->getMessage(),
            ]);
            return;
        } catch (Throwable $e) {
            Log::error('Healthchecker queue probe job aborted (unexpected error)', [
                'check' => $this->checkName,
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        $pinger->ping($url, [
            'check' => $this->checkName,
            'queue' => $this->queue,
            'connection' => $this->connection,
            'host' => gethostname() ?: null,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
