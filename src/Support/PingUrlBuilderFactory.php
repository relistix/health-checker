<?php

namespace Relistix\HealthChecker\Support;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Relistix\HealthChecker\Contracts\PingUrlBuilder as PingUrlBuilderContract;
use Relistix\HealthChecker\Exceptions\HealthCheckerConfigurationException;

class PingUrlBuilderFactory
{
    public function __construct(private ConfigRepository $config)
    {
    }

    public function forQueueCheck(string $name): PingUrlBuilderContract
    {
        return $this->build('queue', $name, $this->config->get("healthchecker.queue_checks.{$name}"));
    }

    public function forMailCheck(string $name): PingUrlBuilderContract
    {
        return $this->build('mail', $name, $this->config->get("healthchecker.mail_checks.{$name}"));
    }

    /**
     * @param array<string, mixed>|null $checkConfig
     */
    private function build(string $type, string $name, ?array $checkConfig): PingUrlBuilderContract
    {
        if ($checkConfig === null) {
            throw HealthCheckerConfigurationException::unknownCheck($type, $name);
        }

        $uuid = $checkConfig['uuid'] ?? null;
        $pingKey = $checkConfig['ping_key'] ?? $this->config->get('healthchecker.ping_key');
        $slug = $checkConfig['slug'] ?? null;

        $hasUuid = !empty($uuid);
        $hasSlugTarget = !empty($pingKey) && !empty($slug);

        if (!$hasUuid && !$hasSlugTarget) {
            throw HealthCheckerConfigurationException::missingPingTarget($type, $name);
        }

        return new PingUrlBuilder(
            (string) $this->config->get('healthchecker.base_url', 'https://hc-ping.com'),
            $uuid,
            $pingKey,
            $slug,
        );
    }
}
