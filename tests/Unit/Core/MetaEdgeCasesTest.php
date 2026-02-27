<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Core;

use Maatify\Exceptions\Contracts\ErrorCategoryInterface;
use Maatify\Exceptions\Contracts\ErrorCodeInterface;
use Maatify\Exceptions\Enum\ErrorCategoryEnum;
use Maatify\Exceptions\Enum\ErrorCodeEnum;
use Maatify\Exceptions\Exception\MaatifyException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MaatifyException::class)]
final class MetaEdgeCasesTest extends TestCase
{
    private function createException(array $meta): MaatifyException
    {
        return new class($meta) extends MaatifyException {
            public function __construct(array $meta)
            {
                parent::__construct('Test', 0, null, null, null, null, null, $meta);
            }
            protected function defaultCategory(): ErrorCategoryInterface { return ErrorCategoryEnum::SYSTEM; }
            protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::MAATIFY_ERROR; }
            protected function defaultHttpStatus(): int { return 500; }
        };
    }

    public function testMetaImmutability(): void
    {
        $meta = ['key' => 'value'];
        $exception = $this->createException($meta);

        $retrieved = $exception->getMeta();
        $retrieved['key'] = 'changed';

        $this->assertSame('value', $exception->getMeta()['key']);
    }

    public function testDeepNestedMeta(): void
    {
        $meta = [
            'level1' => [
                'level2' => [
                    'level3' => 'value'
                ]
            ]
        ];
        $exception = $this->createException($meta);

        $this->assertSame($meta, $exception->getMeta());
    }

    public function testLargeMetaArray(): void
    {
        $meta = array_fill(0, 1000, 'data');
        $exception = $this->createException($meta);

        $this->assertCount(1000, $exception->getMeta());
    }

    public function testEmptyStringKeysAndValues(): void
    {
        $meta = ['' => ''];
        $exception = $this->createException($meta);

        $this->assertSame(['' => ''], $exception->getMeta());
    }
}
