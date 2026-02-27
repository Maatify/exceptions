<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Policy;

use Maatify\Exceptions\Contracts\ApiAwareExceptionInterface;
use Maatify\Exceptions\Contracts\ErrorCategoryInterface;
use Maatify\Exceptions\Contracts\ErrorPolicyInterface;
use Maatify\Exceptions\Enum\ErrorCategoryEnum;
use Maatify\Exceptions\Policy\DefaultEscalationPolicy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefaultEscalationPolicy::class)]
final class DefaultEscalationPolicyBranchTest extends TestCase
{
    private DefaultEscalationPolicy $escalationPolicy;
    private ErrorPolicyInterface $errorPolicy;

    protected function setUp(): void
    {
        $this->escalationPolicy = DefaultEscalationPolicy::default();
        $this->errorPolicy = $this->createMock(ErrorPolicyInterface::class);
    }

    public function testEscalateCategoryWithHigherPreviousSeverity(): void
    {
        // Setup: Current (Low) < Previous (High)
        // Expect: Previous (High)

        $current = ErrorCategoryEnum::VALIDATION; // Severity 50
        $previous = ErrorCategoryEnum::SYSTEM; // Severity 90

        $this->errorPolicy->method('severity')
            ->willReturnMap([
                [$current, 50],
                [$previous, 90]
            ]);

        $result = $this->escalationPolicy->escalateCategory($current, $previous, $this->errorPolicy);

        $this->assertSame($previous, $result);
    }

    public function testEscalateCategoryWithLowerPreviousSeverity(): void
    {
        // Setup: Current (High) > Previous (Low)
        // Expect: Current (High)

        $current = ErrorCategoryEnum::SYSTEM; // 90
        $previous = ErrorCategoryEnum::VALIDATION; // 50

        $this->errorPolicy->method('severity')
            ->willReturnMap([
                [$current, 90],
                [$previous, 50]
            ]);

        $result = $this->escalationPolicy->escalateCategory($current, $previous, $this->errorPolicy);

        $this->assertSame($current, $result);
    }

    public function testEscalateCategoryWithEqualSeverity(): void
    {
        // Setup: Current (50) == Previous (50)
        // Expect: Current (Logic: previous > current ? previous : current)
        // Since 50 > 50 is false, it returns current.

        $current = ErrorCategoryEnum::VALIDATION;
        $previous = ErrorCategoryEnum::VALIDATION;

        $this->errorPolicy->method('severity')
            ->willReturnMap([
                [$current, 50],
                [$previous, 50]
            ]);

        $result = $this->escalationPolicy->escalateCategory($current, $previous, $this->errorPolicy);

        $this->assertSame($current, $result);
    }

    public function testEscalateHttpStatusWrapperLower(): void
    {
        // Current: 200, Previous: 500
        // Expect: 500
        $this->assertSame(500, $this->escalationPolicy->escalateHttpStatus(200, 500));
    }

    public function testEscalateHttpStatusWrapperHigher(): void
    {
        // Current: 503, Previous: 400
        // Expect: 503
        $this->assertSame(503, $this->escalationPolicy->escalateHttpStatus(503, 400));
    }

    public function testEscalateHttpStatusEqual(): void
    {
        // Current: 404, Previous: 404
        // Expect: 404
        $this->assertSame(404, $this->escalationPolicy->escalateHttpStatus(404, 404));
    }

    public function testDeterministicBehavior(): void
    {
        $current = ErrorCategoryEnum::VALIDATION;
        $previous = ErrorCategoryEnum::SYSTEM;

        $this->errorPolicy->method('severity')
            ->willReturnMap([
                [$current, 50],
                [$previous, 90]
            ]);

        $result1 = $this->escalationPolicy->escalateCategory($current, $previous, $this->errorPolicy);
        $result2 = $this->escalationPolicy->escalateCategory($current, $previous, $this->errorPolicy);

        $this->assertSame($result1, $result2);
    }
}
