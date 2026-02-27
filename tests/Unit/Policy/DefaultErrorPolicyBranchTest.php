<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Policy;

use LogicException;
use Maatify\Exceptions\Contracts\ErrorCategoryInterface;
use Maatify\Exceptions\Contracts\ErrorCodeInterface;
use Maatify\Exceptions\Enum\ErrorCategoryEnum;
use Maatify\Exceptions\Enum\ErrorCodeEnum;
use Maatify\Exceptions\Policy\DefaultErrorPolicy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefaultErrorPolicy::class)]
#[CoversClass(ErrorCategoryEnum::class)]
final class DefaultErrorPolicyBranchTest extends TestCase
{
    private DefaultErrorPolicy $policy;

    protected function setUp(): void
    {
        $this->policy = DefaultErrorPolicy::default();
    }

    public function testSeverityDeterminism(): void
    {
        $categories = ErrorCategoryEnum::cases();
        foreach ($categories as $category) {
            $severity1 = $this->policy->severity($category);
            $severity2 = $this->policy->severity($category);
            $this->assertSame($severity1, $severity2, "Severity for {$category->value} should be deterministic");
        }
    }

    public function testSeverityForAllStandardCategories(): void
    {
        // Assert specific values to ensure no regression in default ranking
        $this->assertSame(90, $this->policy->severity(ErrorCategoryEnum::SYSTEM));
        $this->assertSame(80, $this->policy->severity(ErrorCategoryEnum::RATE_LIMIT));
        $this->assertSame(70, $this->policy->severity(ErrorCategoryEnum::AUTHENTICATION));
        $this->assertSame(60, $this->policy->severity(ErrorCategoryEnum::AUTHORIZATION));
        $this->assertSame(50, $this->policy->severity(ErrorCategoryEnum::VALIDATION));
        $this->assertSame(40, $this->policy->severity(ErrorCategoryEnum::BUSINESS_RULE));
        $this->assertSame(30, $this->policy->severity(ErrorCategoryEnum::CONFLICT));
        $this->assertSame(20, $this->policy->severity(ErrorCategoryEnum::NOT_FOUND));
        $this->assertSame(10, $this->policy->severity(ErrorCategoryEnum::UNSUPPORTED));
        $this->assertSame(85, $this->policy->severity(ErrorCategoryEnum::SECURITY));
    }

    public function testSeverityUnknownCategoryReturnsZero(): void
    {
        $unknownCategory = new class implements ErrorCategoryInterface {
            public function getValue(): string { return 'UNKNOWN_CATEGORY'; }
        };
        $this->assertSame(0, $this->policy->severity($unknownCategory));
    }

    public function testValidateSuccessCases(): void
    {
        // System -> MaatifyError
        $this->policy->validate(ErrorCodeEnum::MAATIFY_ERROR, ErrorCategoryEnum::SYSTEM);
        // Validation -> InvalidArgument
        $this->policy->validate(ErrorCodeEnum::INVALID_ARGUMENT, ErrorCategoryEnum::VALIDATION);

        $this->assertTrue(true, 'Validation should pass for correct mappings');
    }

    public function testValidateFailureSystemCategoryMismatch(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Error code "INVALID_ARGUMENT" is not allowed for category "SYSTEM"');

        $this->policy->validate(ErrorCodeEnum::INVALID_ARGUMENT, ErrorCategoryEnum::SYSTEM);
    }

    public function testValidateFailureValidationCategoryMismatch(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Error code "MAATIFY_ERROR" is not allowed for category "VALIDATION"');

        $this->policy->validate(ErrorCodeEnum::MAATIFY_ERROR, ErrorCategoryEnum::VALIDATION);
    }

    public function testValidateUnknownCategoryIgnored(): void
    {
        // If category is not in allowed map, it should pass (open for extension)
        $unknownCategory = new class implements ErrorCategoryInterface {
            public function getValue(): string { return 'CUSTOM_CAT'; }
        };

        $this->policy->validate(ErrorCodeEnum::MAATIFY_ERROR, $unknownCategory);
        $this->assertTrue(true);
    }

    public function testValidateEmptyAllowedListIgnored(): void
    {
        // Logic: array_replace_recursive merges arrays.
        // If 'SYSTEM' => ['MAATIFY_ERROR', ...], and override is 'SYSTEM' => [],
        // array_replace_recursive might NOT replace it with empty array if keyed?
        // But here it is simple array for value.
        // Wait, array_replace_recursive behavior:
        // If value in array1 is array and value in array2 is array, it merges them.
        // So ['a'] and [] result in ['a'] (because [] adds nothing).
        // To CLEAR it, we might need to use a different construction method or pass a value that isn't merged?
        // DefaultErrorPolicy uses array_replace_recursive.

        // Let's create a NEW policy directly instead of using withOverrides to ensure empty array.

        $policy = new DefaultErrorPolicy(
             ['SYSTEM' => 90],
             ['SYSTEM' => []] // Explicitly empty
        );

        // Now SYSTEM should allow anything
        $policy->validate(ErrorCodeEnum::INVALID_ARGUMENT, ErrorCategoryEnum::SYSTEM);
        $this->assertTrue(true, 'Empty allowed list should disable validation for that category');
    }

    public function testWithOverridesMergesCorrectly(): void
    {
        $policy = DefaultErrorPolicy::withOverrides(
            severityOverrides: ['SYSTEM' => 100],
            allowedOverrides: ['SYSTEM' => ['INVALID_ARGUMENT']]
        );

        $this->assertSame(100, $policy->severity(ErrorCategoryEnum::SYSTEM));

        // Should now allow INVALID_ARGUMENT for SYSTEM
        $policy->validate(ErrorCodeEnum::INVALID_ARGUMENT, ErrorCategoryEnum::SYSTEM);

        // Should NOT allow MAATIFY_ERROR for SYSTEM anymore (since we overwrote the list)
        $this->expectException(LogicException::class);
        $policy->validate(ErrorCodeEnum::MAATIFY_ERROR, ErrorCategoryEnum::SYSTEM);
    }
}
