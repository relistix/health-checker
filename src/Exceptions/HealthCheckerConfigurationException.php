<?php

namespace Relistix\HealthChecker\Exceptions;

use RuntimeException;

class HealthCheckerConfigurationException extends RuntimeException
{
    public static function unknownCheck(string $type, string $name): self
    {
        return new self("Healthchecker {$type} check [{$name}] is not configured.");
    }

    public static function missingPingTarget(string $type, string $name): self
    {
        return new self(
            "Healthchecker {$type} check [{$name}] is missing a ping target. " .
            "Provide either a 'uuid' or both 'ping_key' (or top-level 'healthchecker.ping_key') and 'slug'."
        );
    }

    public static function missingQueueName(string $name): self
    {
        return new self("Healthchecker queue check [{$name}] is missing a 'queue' name.");
    }

    public static function missingMailRecipient(string $name): self
    {
        return new self("Healthchecker mail check [{$name}] is missing a 'recipient' email address.");
    }
}
