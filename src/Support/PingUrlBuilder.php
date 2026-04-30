<?php

namespace Relistix\HealthChecker\Support;

use Relistix\HealthChecker\Contracts\PingUrlBuilder as PingUrlBuilderContract;

class PingUrlBuilder implements PingUrlBuilderContract
{
    private string $baseUrl;

    public function __construct(
        string $baseUrl,
        private ?string $uuid = null,
        private ?string $pingKey = null,
        private ?string $slug = null,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function success(): string
    {
        return $this->build('');
    }

    public function fail(): string
    {
        return $this->build('/fail');
    }

    public function start(): string
    {
        return $this->build('/start');
    }

    public function log(): string
    {
        return $this->build('/log');
    }

    public function exitStatus(int $code): string
    {
        return $this->build('/' . $code);
    }

    private function build(string $suffix): string
    {
        if (!empty($this->uuid)) {
            return "{$this->baseUrl}/{$this->uuid}{$suffix}";
        }

        return "{$this->baseUrl}/{$this->pingKey}/{$this->slug}{$suffix}";
    }
}
