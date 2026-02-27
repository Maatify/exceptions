<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Core;

use Maatify\Exceptions\Contracts\ErrorCodeInterface;
use Maatify\Exceptions\Enum\ErrorCategoryEnum;
use Maatify\Exceptions\Enum\ErrorCodeEnum;
use Maatify\Exceptions\Exception\MaatifyException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MaatifyException::class)]
final class DeterministicStressTest extends TestCase
{
    private function createException(string $message, int $code): MaatifyException
    {
        return new class($message, $code) extends MaatifyException {
            protected function defaultCategory(): ErrorCategoryEnum
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

    public function testInstantiationDeterminism(): void
    {
        $iterations = 100;
        $first = null;

        for ($i = 0; $i < $iterations; $i++) {
            $current = $this->createException('Test', 123);

            if ($first === null) {
                $first = $current;
            } else {
                $this->assertSame($first->getMessage(), $current->getMessage());
                $this->assertSame($first->getCode(), $current->getCode());
                $this->assertSame($first->getCategory(), $current->getCategory());
                $this->assertSame($first->getErrorCode(), $current->getErrorCode());
                $this->assertSame($first->getHttpStatus(), $current->getHttpStatus());
                $this->assertSame($first->isSafe(), $current->isSafe());
                $this->assertSame($first->isRetryable(), $current->isRetryable());
            }
        }
    }

    public function testEscalationDeterminismLoop(): void
    {
        // Check that wrapping the same exception repeatedly yields consistent results
        $inner = $this->createException('Inner', 0);

        $prevCategory = null;

        for ($i = 0; $i < 50; $i++) {
            $outer = new class($inner) extends MaatifyException {
                public function __construct(\Throwable $prev) {
                    parent::__construct('Outer', 0, $prev);
                }
                protected function defaultCategory(): ErrorCategoryEnum { return ErrorCategoryEnum::VALIDATION; }
                protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::INVALID_ARGUMENT; }
                protected function defaultHttpStatus(): int { return 400; }
            };

            // Validation (50) wraps System (90) -> Should escalate to System
            $this->assertSame(ErrorCategoryEnum::SYSTEM, $outer->getCategory());

            if ($prevCategory !== null) {
                $this->assertSame($prevCategory, $outer->getCategory());
            }
            $prevCategory = $outer->getCategory();
        }
    }
}
