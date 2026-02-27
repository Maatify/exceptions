<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Application\Error;

use Maatify\Exceptions\Application\Error\ErrorContext;
use Maatify\Exceptions\Application\Error\NormalizedError;
use Maatify\Exceptions\Application\Format\JsonErrorFormatter;
use Maatify\Exceptions\Application\Format\ProblemDetailsFormatter;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
final class DeterminismTest extends TestCase
{
    public function testJsonDeterminism(): void
    {
        $error = new NormalizedError('C', 'M', 400, 'cat', false, true, ['a' => 1]);
        $context = new ErrorContext('trace', 'inst');
        $formatter = new JsonErrorFormatter();

        $response1 = $formatter->format($error, $context);
        $response2 = $formatter->format($error, $context);

        $this->assertSame($response1->getBody(), $response2->getBody());
        $this->assertSame(serialize($response1), serialize($response2));
    }

    public function testRfcDeterminism(): void
    {
        $error = new NormalizedError('C', 'M', 400, 'cat', false, true, ['a' => 1]);
        $context = new ErrorContext('trace', 'inst');
        $formatter = new ProblemDetailsFormatter();

        $response1 = $formatter->format($error, $context);
        $response2 = $formatter->format($error, $context);

        $this->assertSame($response1->getBody(), $response2->getBody());
        $this->assertSame(serialize($response1), serialize($response2));
    }

    public function testNoRandomFieldsInJson(): void
    {
        $error = new NormalizedError('C', 'M', 400, 'cat', false, true, []);
        $context = new ErrorContext();
        $formatter = new JsonErrorFormatter();

        $body = $formatter->format($error, $context)->getBody();

        // Recursively check for timestamps or random looking strings if possible
        // For now, strict check of keys is sufficient as we know the structure
        $this->assertArrayNotHasKey('timestamp', $body);
        $this->assertArrayNotHasKey('time', $body);
        $this->assertArrayNotHasKey('date', $body);
    }
}
