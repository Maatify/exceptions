<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Application\Error;

/**
 * Immutable Value Object representing the final error response.
 *
 * @psalm-immutable
 */
final class ErrorResponseModel
{
    /** @var array<string, string> */
    private array $headers;
    /** @var array<mixed> */
    private array $body;

    /**
     * @param int $status
     * @param array<string, string> $headers
     * @param string $contentType
     * @param array<mixed> $body
     */
    public function __construct(
        private readonly int $status,
        array $headers,
        private readonly string $contentType,
        array $body
    ) {
        $this->headers = [...$headers];
        $this->body = [...$body];
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [...$this->headers];
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @return array<mixed>
     */
    public function getBody(): array
    {
        return [...$this->body];
    }
}
