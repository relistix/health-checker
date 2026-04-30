<?php

namespace Relistix\HealthChecker\Console\Concerns;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Relistix\HealthChecker\Exceptions\HealthCheckerConfigurationException;

/**
 * @mixin Command
 */
trait ReportsConfigurationFailure
{
    private function failWith(HealthCheckerConfigurationException $e, string $name): int
    {
        Log::error($e->getMessage(), ['check' => $name]);
        $this->error($e->getMessage());

        return Command::FAILURE;
    }
}
