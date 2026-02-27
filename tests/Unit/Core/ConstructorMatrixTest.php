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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MaatifyException::class)]
final class ConstructorMatrixTest extends TestCase
{
    /**
     * @return iterable<string, array{
     *     string,
     *     int,
     *     ?\Throwable,
     *     ?ErrorCodeInterface,
     *     ?int,
     *     ?bool,
     *     ?bool,
     *     array<string, mixed>,
     *     ?ErrorPolicyInterface,
     *     ?EscalationPolicyInterface
     * }>
     */
    public static function constructorProvider(): iterable
    {
        // 1. Minimal
        yield 'Minimal' => [
            'Msg', 0, null, null, null, null, null, [], null, null
        ];

        // 2. Full Overrides
        yield 'Full Overrides' => [
            'Msg', 123, new \Exception(), ErrorCodeEnum::DATABASE_CONNECTION_FAILED, 503, true, true, ['k' => 'v'], null, null
        ];

        // 3. Custom Policy
        $policy = new class implements ErrorPolicyInterface {
            public function validate(ErrorCodeInterface $code, ErrorCategoryInterface $category): void {}
            public function severity(ErrorCategoryInterface $category): int { return 100; }
        };
        yield 'Custom Policy' => [
            'Msg', 0, null, ErrorCodeEnum::MAATIFY_ERROR, null, null, null, [], $policy, null
        ];

        // 4. Custom Escalation Policy
        $escPolicy = new class implements EscalationPolicyInterface {
            public function escalateCategory(ErrorCategoryInterface $c, ErrorCategoryInterface $p, ErrorPolicyInterface $pol): ErrorCategoryInterface { return $c; }
            public function escalateHttpStatus(int $c, int $p): int { return $c; }
        };
        yield 'Custom Escalation' => [
            'Msg', 0, null, null, null, null, null, [], null, $escPolicy
        ];

        // 5. Previous ApiAware Exception
        $prevApi = new class extends MaatifyException {
            protected function defaultCategory(): ErrorCategoryInterface { return ErrorCategoryEnum::SYSTEM; }
            protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::MAATIFY_ERROR; }
            protected function defaultHttpStatus(): int { return 500; }
        };
        yield 'Previous ApiAware' => [
            'Msg', 0, $prevApi, null, null, null, null, [], null, null
        ];
    }

    /**
     * @param array<string, mixed> $meta
     */
    #[DataProvider('constructorProvider')]
    public function testConstructorMatrix(
        string $message,
        int $code,
        ?\Throwable $previous,
        ?ErrorCodeInterface $errorCodeOverride,
        ?int $httpStatusOverride,
        ?bool $isSafeOverride,
        ?bool $isRetryableOverride,
        array $meta,
        ?ErrorPolicyInterface $policy,
        ?EscalationPolicyInterface $escalationPolicy
    ): void {
        // We need a concrete implementation to instantiate
        $exception = new class(
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
            protected function defaultCategory(): ErrorCategoryInterface {
                return ErrorCategoryEnum::SYSTEM;
            }

            protected function defaultErrorCode(): ErrorCodeInterface {
                return ErrorCodeEnum::MAATIFY_ERROR;
            }

            protected function defaultHttpStatus(): int {
                return 500;
            }
        };

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());

        if ($errorCodeOverride) {
            $this->assertSame($errorCodeOverride, $exception->getErrorCode());
        }

        if ($httpStatusOverride) {
            $this->assertSame($httpStatusOverride, $exception->getHttpStatus());
        }

        if ($isSafeOverride !== null) {
            $this->assertSame($isSafeOverride, $exception->isSafe());
        }

        if ($isRetryableOverride !== null) {
            $this->assertSame($isRetryableOverride, $exception->isRetryable());
        }

        $this->assertSame($meta, $exception->getMeta());
    }
}
