<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Application\Error;

/**
 * Immutable Value Object representing a normalized error.
 *
 * @psalm-immutable
 */
final class NormalizedError
{

    /**
     * @param string $code
     * @param string $message
     * @param int $status
     * @param string $category
     * @param bool $retryable
     * @param bool $safe
     * @param array<mixed> $meta
     */
    public function __construct(
        private readonly string $code,
        private readonly string $message,
        private readonly int $status,
        private readonly string $category,
        private readonly bool $retryable,
        private readonly bool $safe,
        private array $meta
    ) {
        $this->meta = [...$meta];
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function isRetryable(): bool
    {
        return $this->retryable;
    }

    public function isSafe(): bool
    {
        return $this->safe;
    }

    /**
     * @return array<mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }
}
