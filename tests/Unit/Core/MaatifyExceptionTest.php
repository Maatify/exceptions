<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Core;

use Maatify\Exceptions\Contracts\ErrorCategoryInterface;
use Maatify\Exceptions\Contracts\ErrorCodeInterface;
use Maatify\Exceptions\Contracts\ErrorPolicyInterface;
use Maatify\Exceptions\Contracts\EscalationPolicyInterface;
use Maatify\Exceptions\Enum\ErrorCategoryEnum;
use Maatify\Exceptions\Enum\ErrorCodeEnum;
use Maatify\Exceptions\Exception\MaatifyException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MaatifyException::class)]
final class MaatifyExceptionTest extends TestCase
{
    private function createConcreteException(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        ?ErrorCodeInterface $errorCodeOverride = null,
        ?int $httpStatusOverride = null,
        ?bool $isSafeOverride = null,
        ?bool $isRetryableOverride = null,
        array $meta = [],
        ?ErrorPolicyInterface $policy = null,
        ?EscalationPolicyInterface $escalationPolicy = null
    ): MaatifyException {
        return new class(
            $message,
            $code,
            $previous,
            $errorCodeOverride,
            $httpStatusOverride,
            $isSafeOverride,
            $isRetryableOverride,
            $meta,
            $policy,
            $escalationPolicy
        ) extends MaatifyException {
            protected function defaultCategory(): ErrorCategoryInterface
            {
                return ErrorCategoryEnum::SYSTEM;
            }

            protected function defaultErrorCode(): ErrorCodeInterface
            {
                return ErrorCodeEnum::MAATIFY_ERROR;
            }

            protected function defaultHttpStatus(): int
            {
                return 500;
            }
        };
    }

    public function testConstructorBehavior(): void
    {
        $message = 'Test Message';
        $code = 123;
        $exception = $this->createConcreteException($message, $code);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testDefaultValues(): void
    {
        $exception = $this->createConcreteException();

        $this->assertSame(ErrorCategoryEnum::SYSTEM, $exception->getCategory());
        $this->assertSame(ErrorCodeEnum::MAATIFY_ERROR, $exception->getErrorCode());
        $this->assertSame(500, $exception->getHttpStatus());
        $this->assertFalse($exception->isSafe());
        $this->assertFalse($exception->isRetryable());
        $this->assertSame([], $exception->getMeta());
    }

    public function testOverrides(): void
    {
        $errorCode = ErrorCodeEnum::DATABASE_CONNECTION_FAILED;
        $httpStatus = 503;
        $isSafe = true;
        $isRetryable = true;
        $meta = ['key' => 'value'];

        $exception = $this->createConcreteException(
            errorCodeOverride: $errorCode,
            httpStatusOverride: $httpStatus,
            isSafeOverride: $isSafe,
            isRetryableOverride: $isRetryable,
            meta: $meta
        );

        $this->assertSame($errorCode, $exception->getErrorCode());
        $this->assertSame($httpStatus, $exception->getHttpStatus());
        $this->assertTrue($exception->isSafe());
        $this->assertTrue($exception->isRetryable());
        $this->assertSame($meta, $exception->getMeta());
    }

    public function testPolicyInjection(): void
    {
        $policy = $this->createMock(ErrorPolicyInterface::class);
        $policy->expects($this->once())->method('validate');

        $this->createConcreteException(
            errorCodeOverride: ErrorCodeEnum::MAATIFY_ERROR,
            policy: $policy
        );
    }
}
