<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Application\Error;

/**
 * Immutable Value Object representing the error context.
 *
 * @psalm-immutable
 */
final class ErrorContext
{
    private ?string $traceId;
    private ?string $instance;
    private bool $debug;

    public function __construct(
        ?string $traceId = null,
        ?string $instance = null,
        bool $debug = false
    ) {
        $this->traceId = $traceId;
        $this->instance = $instance;
        $this->debug = $debug;
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
