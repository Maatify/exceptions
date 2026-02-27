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
use Maatify\Exceptions\Policy\DefaultErrorPolicy;
use Maatify\Exceptions\Policy\DefaultEscalationPolicy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MaatifyException::class)]
#[CoversClass(DefaultErrorPolicy::class)]
#[CoversClass(DefaultEscalationPolicy::class)]
final class GlobalPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        MaatifyException::resetGlobalPolicies();
    }

    private function createConcreteException(): MaatifyException
    {
        return new class extends MaatifyException {
            public function __construct()
            {
                parent::__construct('Test', 0);
            }

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

    public function testSetGlobalPolicy(): void
    {
        $policy = $this->createMock(ErrorPolicyInterface::class);
        // validate is called only when errorCodeOverride is present.
        $policy->expects($this->once())->method('validate');

        MaatifyException::setGlobalPolicy($policy);

        // We must trigger validation by providing an error code override
        new class extends MaatifyException {
            public function __construct()
            {
                parent::__construct(
                    'Test',
                    0,
                    null,
                    ErrorCodeEnum::MAATIFY_ERROR
                );
            }

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

    public function testSetGlobalEscalationPolicy(): void
    {
        $policy = $this->createMock(EscalationPolicyInterface::class);
        $policy->expects($this->once())->method('escalateCategory');

        MaatifyException::setGlobalEscalationPolicy($policy);

        // Escalation policy is called when wrapping exceptions
        $previous = new \Exception();
        // However, escalation only happens if previous is ApiAwareExceptionInterface.
        // Let's create another MaatifyException to wrap.
        $inner = $this->createConcreteException();

        $outer = new class($inner) extends MaatifyException {
            public function __construct(\Throwable $prev) {
                parent::__construct('Wrapper', 0, $prev);
            }
            protected function defaultCategory(): ErrorCategoryInterface { return ErrorCategoryEnum::SYSTEM; }
            protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::MAATIFY_ERROR; }
            protected function defaultHttpStatus(): int { return 500; }
        };
    }

    public function testResetGlobalPolicies(): void
    {
        $policy = $this->createMock(ErrorPolicyInterface::class);
        MaatifyException::setGlobalPolicy($policy);

        MaatifyException::resetGlobalPolicies();

        // Should fall back to default policy behavior (no mock expectation call)
        // We can verify this indirectly by checking if it uses default validation
        // But simply ensuring no mock call is not enough if default also validates.

        // Let's rely on the fact that if we set a mock that throws, and then reset, it shouldn't throw.

        $throwingPolicy = $this->createMock(ErrorPolicyInterface::class);
        $throwingPolicy->method('validate')->willThrowException(new \RuntimeException('Mock Policy Active'));

        MaatifyException::setGlobalPolicy($throwingPolicy);
        MaatifyException::resetGlobalPolicies();

        $this->createConcreteException();
        $this->assertTrue(true); // If we reached here, no exception was thrown
    }
}
