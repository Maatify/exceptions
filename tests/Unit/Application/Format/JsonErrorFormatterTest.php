<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Application\Format;

use Maatify\Exceptions\Application\Error\ErrorContext;
use Maatify\Exceptions\Application\Error\NormalizedError;
use Maatify\Exceptions\Application\Format\JsonErrorFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Maatify\Exceptions\Application\Format\JsonErrorFormatter::class)]

final class JsonErrorFormatterTest extends TestCase
{
    private JsonErrorFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new JsonErrorFormatter();
    }

    public function testBasicJsonFormatting(): void
    {
        $error = new NormalizedError(
            'CODE',
            'Message',
            400,
            'category',
            false,
            true,
            []
        );
        $context = new ErrorContext();

        $response = $this->formatter->format($error, $context);

        $this->assertSame(400, $response->getStatus());
        $this->assertSame('application/json; charset=utf-8', $response->getContentType());
        $this->assertSame([], $response->getHeaders());

        $body = $response->getBody();
        $this->assertArrayHasKey('error', $body);
        $this->assertArrayNotHasKey('trace_id', $body);

        $errorBody = $body['error'];
        $this->assertSame('CODE', $errorBody['code']);
        $this->assertSame('Message', $errorBody['message']);
        $this->assertSame(400, $errorBody['status']);
        $this->assertSame('category', $errorBody['category']);
        $this->assertFalse($errorBody['retryable']);
        $this->assertTrue($errorBody['safe']);
        $this->assertSame([], $errorBody['meta']);
    }

    public function testTraceIdInclusion(): void
    {
        $error = new NormalizedError('C', 'M', 500, 'cat', false, true, []);
        $context = new ErrorContext('abc123trace');

        $response = $this->formatter->format($error, $context);
        $body = $response->getBody();

        $this->assertArrayHasKey('trace_id', $body);
        $this->assertSame('abc123trace', $body['trace_id']);
    }

    public function testMetaAlwaysPresent(): void
    {
        $error = new NormalizedError('C', 'M', 500, 'cat', false, true, []);
        $context = new ErrorContext();

        $response = $this->formatter->format($error, $context);
        $body = $response->getBody();

        $this->assertArrayHasKey('meta', $body['error']);
        $this->assertIsArray($body['error']['meta']);
    }

    public function testStatusConsistency(): void
    {
        $error = new NormalizedError('C', 'M', 418, 'cat', false, true, []);
        $context = new ErrorContext();

        $response = $this->formatter->format($error, $context);

        $this->assertSame(418, $response->getStatus());
        $this->assertSame(418, $response->getBody()['error']['status']);
    }
}
