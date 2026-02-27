<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Application\Error;

/**
 * Immutable Value Object representing the error context.
 *
 * @psalm-immutable
 */
final readonly class ErrorContext
{

    public function __construct(
        private ?string $traceId = null,
        private ?string $instance = null,
        private bool $debug = false
    ) {
    }

    public function getTraceId(): ?string
    {
        return $this->traceId;
    }

    public function getInstance(): ?string
    {
        return $this->instance;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }
}
