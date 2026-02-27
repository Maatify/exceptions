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
    private int $status;
    /** @var array<string, string> */
    private array $headers;
    private string $contentType;
    /** @var array<mixed> */
    private array $body;

    /**
     * @param int $status
     * @param array<string, string> $headers
     * @param string $contentType
     * @param array<mixed> $body
     */
    public function __construct(
        int $status,
        array $headers,
        string $contentType,
        array $body
    ) {
        $this->status = $status;
        $this->headers = $headers;
        $this->contentType = $contentType;
        $this->body = $body;
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
        return $this->headers;
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
        return $this->body;
    }
}
