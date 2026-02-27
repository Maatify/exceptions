<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Core;

use Maatify\Exceptions\Contracts\ErrorCategoryInterface;
use Maatify\Exceptions\Contracts\ErrorCodeInterface;
use Maatify\Exceptions\Enum\ErrorCategoryEnum;
use Maatify\Exceptions\Enum\ErrorCodeEnum;
use Maatify\Exceptions\Exception\BusinessRule\BusinessRuleMaatifyException;
use Maatify\Exceptions\Exception\MaatifyException;
use Maatify\Exceptions\Exception\System\DatabaseConnectionMaatifyException;
use Maatify\Exceptions\Exception\Validation\InvalidArgumentMaatifyException;
use Maatify\Exceptions\Policy\DefaultEscalationPolicy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MaatifyException::class)]
#[CoversClass(DefaultEscalationPolicy::class)]
final class EscalationTest extends TestCase
{
    private function createBusinessException(?\Throwable $previous = null): MaatifyException
    {
        return new class($previous) extends BusinessRuleMaatifyException {
            public function __construct(?\Throwable $previous = null)
            {
                parent::__construct('Business Error', 0, $previous);
            }
        };
    }

    public function testWrapLowerSeverityInHigher(): void
    {
        // Validation (Severity 50) wrapped in System (Severity 90)
        // Should be System (90)

        $lowSeverity = new InvalidArgumentMaatifyException('Invalid Input');
        $highSeverity = new DatabaseConnectionMaatifyException('DB Error', 0, $lowSeverity);

        $this->assertSame(ErrorCategoryEnum::SYSTEM, $highSeverity->getCategory());
        $this->assertSame(503, $highSeverity->getHttpStatus());
    }

    public function testWrapHigherSeverityInLower(): void
    {
        // System (Severity 90) wrapped in Business (Severity 40)
        // Should escalate to System (90)

        $highSeverity = new DatabaseConnectionMaatifyException('DB Error');
        $lowSeverity = $this->createBusinessException($highSeverity);

        $this->assertSame(ErrorCategoryEnum::SYSTEM, $lowSeverity->getCategory());

        // Status should be escalated too (max of both)
        // System is 503, Business is 422
        $this->assertSame(503, $lowSeverity->getHttpStatus());
    }

    public function testSeverityCannotBeDowngraded(): void
    {
        // System (90) -> Business (40) -> Validation (50)
        // The middle one escalates to System.
        // The outer one wraps the middle one (which is now effectively System).
        // So outer one should also escalate to System.

        $system = new DatabaseConnectionMaatifyException('Root Cause');
        $business = $this->createBusinessException($system);

        // Verify intermediate escalation
        $this->assertSame(ErrorCategoryEnum::SYSTEM, $business->getCategory());

        $validation = new InvalidArgumentMaatifyException('Outer', 0, $business);

        $this->assertSame(ErrorCategoryEnum::SYSTEM, $validation->getCategory());
        $this->assertSame(503, $validation->getHttpStatus());
    }

    public function testEscalationIsDeterministic(): void
    {
        $system = new DatabaseConnectionMaatifyException('Root Cause');
        $business = $this->createBusinessException($system);

        $category1 = $business->getCategory();
        $category2 = $business->getCategory();

        $this->assertSame($category1, $category2);
        $this->assertSame(ErrorCategoryEnum::SYSTEM, $category1);
    }
}
