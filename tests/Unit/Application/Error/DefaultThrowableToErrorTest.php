<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Application\Error;

use Maatify\Exceptions\Application\Error\DefaultThrowableToError;
use Maatify\Exceptions\Application\Error\NormalizedError;
use Maatify\Exceptions\Contracts\ErrorCategoryInterface;
use Maatify\Exceptions\Contracts\ErrorCodeInterface;
use Maatify\Exceptions\Contracts\ErrorPolicyInterface;
use Maatify\Exceptions\Contracts\EscalationPolicyInterface;
use Maatify\Exceptions\Exception\MaatifyException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(\Maatify\Exceptions\Application\Error\DefaultThrowableToError::class)]

final class DefaultThrowableToErrorTest extends TestCase
{
    private DefaultThrowableToError $mapper;

    protected function setUp(): void
    {
        $this->mapper = new DefaultThrowableToError();
    }

    public function testMaatifyExceptionMapping(): void
    {
        // Define simple implementations for testing
        $mockErrorCode = new class implements ErrorCodeInterface {
            public function getValue(): string { return 'VALIDATION_FAILED'; }
        };

        // Create anonymous class extending MaatifyException
        $exception = new class(
            'Invalid input',
            0,
            null,
            $mockErrorCode,
            400,
            true, // isSafe
            false, // isRetryable
            ['field' => 'email']
        ) extends MaatifyException {
            protected function defaultErrorCode(): ErrorCodeInterface
            {
                return new class implements ErrorCodeInterface { public function getValue(): string { return 'DEFAULT'; } };
            }

            protected function defaultCategory(): ErrorCategoryInterface
            {
                return new class implements ErrorCategoryInterface { public function getValue(): string { return 'validation'; } };
            }

            protected function defaultHttpStatus(): int
            {
                return 400;
            }
        };

        $normalized = $this->mapper->map($exception);

        $this->assertSame('VALIDATION_FAILED', $normalized->getCode());
        $this->assertSame('Invalid input', $normalized->getMessage());
        $this->assertSame(400, $normalized->getStatus());
        $this->assertSame('validation', $normalized->getCategory());
        $this->assertFalse($normalized->isRetryable());
        $this->assertTrue($normalized->isSafe());
        $this->assertSame(['field' => 'email'], $normalized->getMeta());
    }

    public function testExternalThrowableFallback(): void
    {
        $exception = new RuntimeException('DB exploded');

        $normalized = $this->mapper->map($exception);

        $this->assertSame('INTERNAL_ERROR', $normalized->getCode());
        $this->assertSame('An unexpected error occurred.', $normalized->getMessage());
        $this->assertSame(500, $normalized->getStatus());
        $this->assertSame('internal', $normalized->getCategory());
        $this->assertFalse($normalized->isRetryable());
        $this->assertTrue($normalized->isSafe());
        $this->assertSame([], $normalized->getMeta());
    }

    public function testDeterminism(): void
    {
        $exception = new RuntimeException('DB exploded');

        $normalized1 = $this->mapper->map($exception);
        $normalized2 = $this->mapper->map($exception);

        $this->assertEquals($normalized1, $normalized2);
    }

    public function testExternalExceptionMessageNotLeaked(): void
    {
        $exception = new RuntimeException("Sensitive DB error");

        $normalized = $this->mapper->map($exception);

        $this->assertSame("An unexpected error occurred.", $normalized->getMessage());
    }
}
