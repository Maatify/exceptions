<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Contracts;

use Maatify\Exceptions\Contracts\ApiAwareExceptionInterface;
use Maatify\Exceptions\Contracts\ErrorCategoryInterface;
use Maatify\Exceptions\Contracts\ErrorCodeInterface;
use Maatify\Exceptions\Exception\MaatifyException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MaatifyException::class)]
final class InterfaceComplianceTest extends TestCase
{
    public function testApiAwareExceptionInterfaceContract(): void
    {
        $exception = new class extends MaatifyException {
            protected function defaultCategory(): ErrorCategoryInterface
            {
                return new class implements ErrorCategoryInterface {
                    public function getValue(): string { return 'TEST_CATEGORY'; }
                };
            }
            protected function defaultErrorCode(): ErrorCodeInterface
            {
                return new class implements ErrorCodeInterface {
                    public function getValue(): string { return 'TEST_CODE'; }
                };
            }
            protected function defaultHttpStatus(): int { return 418; }
            protected function defaultIsSafe(): bool { return true; }
            protected function defaultIsRetryable(): bool { return true; }
        };

        $this->assertInstanceOf(ApiAwareExceptionInterface::class, $exception);

        $this->assertSame(418, $exception->getHttpStatus());
        $this->assertSame('TEST_CODE', $exception->getErrorCode()->getValue());
        $this->assertSame('TEST_CATEGORY', $exception->getCategory()->getValue());
        $this->assertTrue($exception->isSafe());
        $this->assertTrue($exception->isRetryable());
        $this->assertSame([], $exception->getMeta());
    }

    public function testCustomEnumCompatibility(): void
    {
        // Simulate a custom enum for ErrorCode
        $customCode = new class implements ErrorCodeInterface {
            public function getValue(): string { return 'CUSTOM_ENUM_VAL'; }
        };

        $exception = new class($customCode) extends MaatifyException {
            public function __construct(ErrorCodeInterface $code) {
                // Bypass validation for this test since we are injecting a custom code
                // that might not be in the default policy allowed list.
                // However, MaatifyException validates immediately.
                // To test custom enums, we need to ensure the policy allows it OR bypass policy.
                // Since we can't change source, we must assume custom enums are used WITH a policy that allows them.

                // So we will pass a permissive policy.
                parent::__construct(
                    'Test',
                    0,
                    null,
                    $code,
                    null,
                    null,
                    null,
                    [],
                    new class implements \Maatify\Exceptions\Contracts\ErrorPolicyInterface {
                        public function validate(ErrorCodeInterface $code, ErrorCategoryInterface $category): void {}
                        public function severity(ErrorCategoryInterface $category): int { return 10; }
                    }
                );
            }
            protected function defaultCategory(): ErrorCategoryInterface {
                return new class implements ErrorCategoryInterface {
                    public function getValue(): string { return 'CUSTOM_CAT'; }
                };
            }
            protected function defaultErrorCode(): ErrorCodeInterface {
                 return new class implements ErrorCodeInterface {
                    public function getValue(): string { return 'DEFAULT'; }
                };
            }
            protected function defaultHttpStatus(): int { return 500; }
        };

        $this->assertSame($customCode, $exception->getErrorCode());
    }
}
