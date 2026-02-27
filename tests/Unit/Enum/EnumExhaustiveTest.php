<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Enum;

use Maatify\Exceptions\Enum\ErrorCategoryEnum;
use Maatify\Exceptions\Enum\ErrorCodeEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ErrorCategoryEnum::class)]
#[CoversClass(ErrorCodeEnum::class)]
final class EnumExhaustiveTest extends TestCase
{
    public function testErrorCategoryEnumValues(): void
    {
        $cases = ErrorCategoryEnum::cases();
        foreach ($cases as $case) {
            $this->assertSame($case->name, $case->value);
            $this->assertSame($case->value, $case->getValue());
        }
    }

    public function testErrorCodeEnumValues(): void
    {
        $cases = ErrorCodeEnum::cases();
        foreach ($cases as $case) {
            $this->assertSame($case->name, $case->value);
            $this->assertSame($case->value, $case->getValue());
        }
    }

    public function testEnumCasesAreNotEmpty(): void
    {
        $this->assertNotEmpty(ErrorCategoryEnum::cases());
        $this->assertNotEmpty(ErrorCodeEnum::cases());
    }
}
