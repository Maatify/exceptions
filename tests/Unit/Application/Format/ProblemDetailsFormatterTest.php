<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Application\Format;

use Maatify\Exceptions\Application\Error\ErrorContext;
use Maatify\Exceptions\Application\Error\NormalizedError;
use Maatify\Exceptions\Application\Format\ProblemDetailsFormatter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Maatify\Exceptions\Application\Format\ProblemDetailsFormatter
 */
final class ProblemDetailsFormatterTest extends TestCase
{
    private ProblemDetailsFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new ProblemDetailsFormatter();
    }

    public function testBasicRfcStructure(): void
    {
        $error = new NormalizedError(
            'CODE',
            'Message',
            400,
            'validation',
            false,
            true,
            []
        );
        $context = new ErrorContext();

        $response = $this->formatter->format($error, $context);

        $this->assertSame(400, $response->getStatus());
        $this->assertSame('application/problem+json; charset=utf-8', $response->getContentType());
        $this->assertSame([], $response->getHeaders());

        $body = $response->getBody();
        $this->assertSame('https://maatify.dev/problems/validation', $body['type']);
        $this->assertSame('Validation failed', $body['title']);
        $this->assertSame(400, $body['status']);
        $this->assertSame('Message', $body['detail']);
    }

    public function testExtensionsAlwaysPresent(): void
    {
        $error = new NormalizedError('C', 'M', 500, 'internal', false, true, ['meta' => 'data']);
        $context = new ErrorContext();

        $response = $this->formatter->format($error, $context);
        $body = $response->getBody();

        $this->assertArrayHasKey('extensions', $body);
        $extensions = $body['extensions'];

        $this->assertSame('C', $extensions['code']);
        $this->assertSame('internal', $extensions['category']);
        $this->assertFalse($extensions['retryable']);
        $this->assertTrue($extensions['safe']);
        $this->assertSame(['meta' => 'data'], $extensions['meta']);
    }

    public function testInstanceInclusion(): void
    {
        $error = new NormalizedError('C', 'M', 400, 'validation', false, true, []);
        $context = new ErrorContext(null, '/users/1');

        $response = $this->formatter->format($error, $context);
        $body = $response->getBody();

        $this->assertSame('/users/1', $body['instance']);
    }

    public function testInstanceOmittedWhenNull(): void
    {
        $error = new NormalizedError('C', 'M', 400, 'validation', false, true, []);
        $context = new ErrorContext();

        $response = $this->formatter->format($error, $context);
        $body = $response->getBody();

        $this->assertArrayNotHasKey('instance', $body);
    }

    /**
     * @dataProvider categoryTitleProvider
     */
    public function testTitleMapping(string $category, string $expectedTitle): void
    {
        $error = new NormalizedError('C', 'M', 400, $category, false, true, []);
        $context = new ErrorContext();

        $response = $this->formatter->format($error, $context);
        $body = $response->getBody();

        $this->assertSame($expectedTitle, $body['title']);
    }

    public static function categoryTitleProvider(): array
    {
        return [
            ['validation', 'Validation failed'],
            ['authentication', 'Authentication required'],
            ['authorization', 'Permission denied'],
            ['conflict', 'Conflict'],
            ['internal', 'Internal error'],
            ['unknown', 'Unknown'], // Uses ucfirst fallback
        ];
    }
}
