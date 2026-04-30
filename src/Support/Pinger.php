<?php

namespace Relistix\HealthChecker\Support;

use Illuminate\Http\Client\Factory as HttpFactory;
use Psr\Log\LoggerInterface;
use Throwable;

class Pinger
{
    public function __construct(
        private HttpFactory $http,
        private LoggerInterface $logger,
        private int $timeout = 5,
        private int $retries = 0,
    ) {
    }

    /**
     * POST to a Healthchecks.io ping URL with a small JSON body.
     *
     * Failures are logged and swallowed — pinging must never break the
     * host application's scheduler.
     *
     * @param array<string, mixed> $body
     */
    public function ping(string $url, array $body = []): bool
    {
        try {
            $response = $this->http
                ->timeout($this->timeout)
                ->retry(max(1, $this->retries + 1), 100, throw: false)
                ->asJson()
                ->post($url, $body);

            if ($response->successful()) {
                return true;
            }

            $this->logger->warning('Healthchecks.io ping returned non-success status', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (Throwable $e) {
            $this->logger->error('Healthchecks.io ping failed', [
                'url' => $url,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
