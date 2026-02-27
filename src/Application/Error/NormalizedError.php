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
    private string $code;
    private string $message;
    private int $status;
    private string $category;
    private bool $retryable;
    private bool $safe;
    /** @var array<mixed> */
    private array $meta;

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
        string $code,
        string $message,
        int $status,
        string $category,
        bool $retryable,
        bool $safe,
        array $meta
    ) {
        $this->code = $code;
        $this->message = $message;
        $this->status = $status;
        $this->category = $category;
        $this->retryable = $retryable;
        $this->safe = $safe;
        $this->meta = $meta;
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
