<?php

namespace Relistix\HealthChecker\Contracts;

interface PingUrlBuilder
{
    public function success(): string;

    public function fail(): string;

    public function start(): string;

    public function log(): string;

    public function exitStatus(int $code): string;
}
