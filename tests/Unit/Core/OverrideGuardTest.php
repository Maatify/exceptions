<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Core;

use LogicException;
use Maatify\Exceptions\Contracts\ErrorCodeInterface;
use Maatify\Exceptions\Enum\ErrorCodeEnum;
use Maatify\Exceptions\Exception\Validation\ValidationMaatifyException;
use Maatify\Exceptions\Policy\DefaultErrorPolicy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MaatifyException::class)]
#[CoversClass(DefaultErrorPolicy::class)]
final class OverrideGuardTest extends TestCase
{
    private function createValidationException(
        ?ErrorCodeInterface $errorCode = null,
        ?int $httpStatus = null
    ): ValidationMaatifyException {
        return new class($errorCode, $httpStatus) extends ValidationMaatifyException {
            public function __construct(
                ?ErrorCodeInterface $errorCode,
                ?int $httpStatus
            ) {
                parent::__construct(
                    'Validation Error',
                    0,
                    null,
                    $errorCode,
                    $httpStatus
                );
            }

            protected function defaultErrorCode(): ErrorCodeInterface
            {
                return ErrorCodeEnum::INVALID_ARGUMENT;
            }
        };
    }

    public function testInvalidHttpStatusOverride(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('HttpStatus override 500 must belong to same class family as default 400');

        // Validation default is 400 (Client Error)
        // Attempting to override with 500 (Server Error) should fail
        $this->createValidationException(null, 500);
    }

    public function testValidHttpStatusOverride(): void
    {
        // Validation default is 400
        // Overriding with 422 (Unprocessable Entity) is allowed (both 4xx)
        $exception = $this->createValidationException(null, 422);

        $this->assertSame(422, $exception->getHttpStatus());
    }

    public function testCategoryMismatchProtection(): void
    {
        $this->expectException(LogicException::class);
        // We expect: Error code "DATABASE_CONNECTION_FAILED" is not allowed for category "VALIDATION".
        $this->expectExceptionMessage('Error code "DATABASE_CONNECTION_FAILED" is not allowed for category "VALIDATION"');

        // DATABASE_CONNECTION_FAILED belongs to SYSTEM, not VALIDATION
        $this->createValidationException(ErrorCodeEnum::DATABASE_CONNECTION_FAILED);
    }

    public function testValidErrorCodeOverride(): void
    {
        // INVALID_ARGUMENT is allowed for VALIDATION (default)
        // Let's assume we had another validation error code, but for now just use the default one explicitly
        $exception = $this->createValidationException(ErrorCodeEnum::INVALID_ARGUMENT);

        $this->assertSame(ErrorCodeEnum::INVALID_ARGUMENT, $exception->getErrorCode());
    }
}
