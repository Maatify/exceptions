<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Exception;

use Maatify\Exceptions\Contracts\ErrorCodeInterface;
use Maatify\Exceptions\Enum\ErrorCategoryEnum;
use Maatify\Exceptions\Enum\ErrorCodeEnum;
use Maatify\Exceptions\Exception\Authentication\SessionExpiredMaatifyException;
use Maatify\Exceptions\Exception\Authentication\UnauthorizedMaatifyException;
use Maatify\Exceptions\Exception\Authorization\ForbiddenMaatifyException;
use Maatify\Exceptions\Exception\Conflict\GenericConflictMaatifyException;
use Maatify\Exceptions\Exception\MaatifyException;
use Maatify\Exceptions\Exception\NotFound\ResourceNotFoundMaatifyException;
use Maatify\Exceptions\Exception\RateLimit\TooManyRequestsMaatifyException;
use Maatify\Exceptions\Exception\System\DatabaseConnectionMaatifyException;
use Maatify\Exceptions\Exception\Unsupported\UnsupportedOperationMaatifyException;
use Maatify\Exceptions\Exception\Validation\InvalidArgumentMaatifyException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SessionExpiredMaatifyException::class)]
#[CoversClass(UnauthorizedMaatifyException::class)]
#[CoversClass(ForbiddenMaatifyException::class)]
#[CoversClass(GenericConflictMaatifyException::class)]
#[CoversClass(ResourceNotFoundMaatifyException::class)]
#[CoversClass(TooManyRequestsMaatifyException::class)]
#[CoversClass(DatabaseConnectionMaatifyException::class)]
#[CoversClass(UnsupportedOperationMaatifyException::class)]
#[CoversClass(InvalidArgumentMaatifyException::class)]
final class AllExceptionInstantiationTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string<MaatifyException>}>
     */
    public static function exceptionClassProvider(): iterable
    {
        yield 'SessionExpired' => [SessionExpiredMaatifyException::class];
        yield 'Unauthorized' => [UnauthorizedMaatifyException::class];
        yield 'Forbidden' => [ForbiddenMaatifyException::class];
        yield 'Conflict' => [GenericConflictMaatifyException::class];
        yield 'ResourceNotFound' => [ResourceNotFoundMaatifyException::class];
        yield 'TooManyRequests' => [TooManyRequestsMaatifyException::class];
        yield 'DatabaseConnection' => [DatabaseConnectionMaatifyException::class];
        yield 'UnsupportedOperation' => [UnsupportedOperationMaatifyException::class];
        yield 'InvalidArgument' => [InvalidArgumentMaatifyException::class];
    }

    /**
     * @param class-string<MaatifyException> $class
     */
    #[DataProvider('exceptionClassProvider')]
    public function testInstantiation(string $class): void
    {
        $exception = new $class('Test Message');

        $this->assertInstanceOf(MaatifyException::class, $exception);
        $this->assertSame('Test Message', $exception->getMessage());

        // Ensure core methods don't throw
        $exception->getCategory();
        $exception->getErrorCode();
        $exception->getHttpStatus();
        $exception->isSafe();
        $exception->isRetryable();
    }
}
