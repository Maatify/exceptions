<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Application\Error;

use Maatify\Exceptions\Application\Error\ErrorContext;
use Maatify\Exceptions\Application\Error\ErrorResponseModel;
use Maatify\Exceptions\Application\Error\ErrorSerializer;
use Maatify\Exceptions\Application\Error\NormalizedError;
use Maatify\Exceptions\Application\Error\ThrowableToErrorInterface;
use Maatify\Exceptions\Application\Format\FormatterInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Maatify\Exceptions\Application\Error\ErrorSerializer
 */
final class ErrorSerializerTest extends TestCase
{
    private ThrowableToErrorInterface $mapper;
    private FormatterInterface $formatter;
    private ErrorSerializer $serializer;

    protected function setUp(): void
    {
        $this->mapper = $this->createMock(ThrowableToErrorInterface::class);
        $this->formatter = $this->createMock(FormatterInterface::class);
        $this->serializer = new ErrorSerializer($this->mapper, $this->formatter);
    }

    public function testSerializeUsesMapperAndFormatter(): void
    {
        $exception = new RuntimeException('Error');
        $normalized = new NormalizedError('C', 'M', 500, 'cat', false, true, []);
        $responseModel = new ErrorResponseModel(500, [], 'application/json', []);
        $context = new ErrorContext();

        $this->mapper->expects($this->once())
            ->method('map')
            ->with($exception)
            ->willReturn($normalized);

        $this->formatter->expects($this->once())
            ->method('format')
            ->with($normalized, $context)
            ->willReturn($responseModel);

        $result = $this->serializer->serialize($exception, $context);

        $this->assertSame($responseModel, $result);
    }

    public function testSerializeCreatesDefaultContextIfNull(): void
    {
        $exception = new RuntimeException('Error');
        $normalized = new NormalizedError('C', 'M', 500, 'cat', false, true, []);
        $responseModel = new ErrorResponseModel(500, [], 'application/json', []);

        $this->mapper->method('map')->willReturn($normalized);

        $this->formatter->expects($this->once())
            ->method('format')
            ->with($normalized, $this->isInstanceOf(ErrorContext::class))
            ->willReturn($responseModel);

        $result = $this->serializer->serialize($exception);

        $this->assertSame($responseModel, $result);
    }
}
