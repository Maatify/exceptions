<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Application\Error;

use Maatify\Exceptions\Application\Error\NormalizedError;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Maatify\Exceptions\Application\Error\NormalizedError
 */
final class NormalizedErrorTest extends TestCase
{
    public function testConstructionIntegrity(): void
    {
        $error = new NormalizedError(
            'CODE',
            'Message',
            400,
            'category',
            true,
            false,
            ['foo' => 'bar']
        );

        $this->assertSame('CODE', $error->getCode());
        $this->assertSame('Message', $error->getMessage());
        $this->assertSame(400, $error->getStatus());
        $this->assertSame('category', $error->getCategory());
        $this->assertTrue($error->isRetryable());
        $this->assertFalse($error->isSafe());
        $this->assertSame(['foo' => 'bar'], $error->getMeta());
    }

    public function testMetaAlwaysPresent(): void
    {
        $error = new NormalizedError(
            'CODE',
            'Message',
            400,
            'category',
            false,
            true,
            []
        );

        $this->assertSame([], $error->getMeta());
    }

    public function testImmutability(): void
    {
        $error = new NormalizedError(
            'CODE',
            'Message',
            400,
            'category',
            false,
            true,
            ['foo' => 'bar']
        );

        $meta = $error->getMeta();
        $meta['foo'] = 'baz';

        $this->assertSame(['foo' => 'bar'], $error->getMeta());
    }
}
