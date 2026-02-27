<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Exception;

use Maatify\Exceptions\Contracts\ErrorCategoryInterface;
use Maatify\Exceptions\Contracts\ErrorCodeInterface;
use Maatify\Exceptions\Enum\ErrorCategoryEnum;
use Maatify\Exceptions\Enum\ErrorCodeEnum;
use Maatify\Exceptions\Exception\Authentication\AuthenticationMaatifyException;
use Maatify\Exceptions\Exception\Authentication\SessionExpiredMaatifyException;
use Maatify\Exceptions\Exception\Authentication\UnauthorizedMaatifyException;
use Maatify\Exceptions\Exception\Authorization\AuthorizationMaatifyException;
use Maatify\Exceptions\Exception\Authorization\ForbiddenMaatifyException;
use Maatify\Exceptions\Exception\BusinessRule\BusinessRuleMaatifyException;
use Maatify\Exceptions\Exception\Conflict\ConflictMaatifyException;
use Maatify\Exceptions\Exception\Conflict\GenericConflictMaatifyException;
use Maatify\Exceptions\Exception\MaatifyException;
use Maatify\Exceptions\Exception\NotFound\NotFoundMaatifyException;
use Maatify\Exceptions\Exception\NotFound\ResourceNotFoundMaatifyException;
use Maatify\Exceptions\Exception\RateLimit\RateLimitMaatifyException;
use Maatify\Exceptions\Exception\RateLimit\TooManyRequestsMaatifyException;
use Maatify\Exceptions\Exception\Security\SecurityMaatifyException;
use Maatify\Exceptions\Exception\System\DatabaseConnectionMaatifyException;
use Maatify\Exceptions\Exception\System\SystemMaatifyException;
use Maatify\Exceptions\Exception\Unsupported\UnsupportedMaatifyException;
use Maatify\Exceptions\Exception\Unsupported\UnsupportedOperationMaatifyException;
use Maatify\Exceptions\Exception\Validation\InvalidArgumentMaatifyException;
use Maatify\Exceptions\Exception\Validation\ValidationMaatifyException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthenticationMaatifyException::class)]
#[CoversClass(SessionExpiredMaatifyException::class)]
#[CoversClass(UnauthorizedMaatifyException::class)]
#[CoversClass(AuthorizationMaatifyException::class)]
#[CoversClass(ForbiddenMaatifyException::class)]
#[CoversClass(BusinessRuleMaatifyException::class)]
#[CoversClass(ConflictMaatifyException::class)]
#[CoversClass(GenericConflictMaatifyException::class)]
#[CoversClass(MaatifyException::class)]
#[CoversClass(NotFoundMaatifyException::class)]
#[CoversClass(ResourceNotFoundMaatifyException::class)]
#[CoversClass(RateLimitMaatifyException::class)]
#[CoversClass(TooManyRequestsMaatifyException::class)]
#[CoversClass(SecurityMaatifyException::class)]
#[CoversClass(SystemMaatifyException::class)]
#[CoversClass(DatabaseConnectionMaatifyException::class)]
#[CoversClass(UnsupportedMaatifyException::class)]
#[CoversClass(UnsupportedOperationMaatifyException::class)]
#[CoversClass(ValidationMaatifyException::class)]
#[CoversClass(InvalidArgumentMaatifyException::class)]
#[CoversClass(ErrorCategoryEnum::class)]
#[CoversClass(ErrorCodeEnum::class)]
final class ExceptionFamiliesTest extends TestCase
{
    /**
     * @return array<string, array{
     *     class-string<MaatifyException>,
     *     ErrorCategoryInterface,
     *     int,
     *     ?ErrorCodeInterface,
     *     bool,
     *     bool
     * }>
     */
    public static function exceptionProvider(): array
    {
        return [
            'System: Generic' => [
                get_class(new class extends SystemMaatifyException {
                    protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::MAATIFY_ERROR; }
                }),
                ErrorCategoryEnum::SYSTEM,
                500,
                ErrorCodeEnum::MAATIFY_ERROR,
                false, // isSafe
                false  // isRetryable
            ],
            'System: DatabaseConnection' => [
                DatabaseConnectionMaatifyException::class,
                ErrorCategoryEnum::SYSTEM,
                503,
                ErrorCodeEnum::DATABASE_CONNECTION_FAILED,
                false,
                false
            ],
            'Validation: Generic' => [
                get_class(new class extends ValidationMaatifyException {
                    protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::INVALID_ARGUMENT; }
                }),
                ErrorCategoryEnum::VALIDATION,
                400,
                ErrorCodeEnum::INVALID_ARGUMENT,
                true,
                false
            ],
            'Validation: InvalidArgument' => [
                InvalidArgumentMaatifyException::class,
                ErrorCategoryEnum::VALIDATION,
                400,
                ErrorCodeEnum::INVALID_ARGUMENT,
                true,
                false
            ],
            'Authentication: Generic' => [
                get_class(new class extends AuthenticationMaatifyException {
                    protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::UNAUTHORIZED; }
                }),
                ErrorCategoryEnum::AUTHENTICATION,
                401,
                ErrorCodeEnum::UNAUTHORIZED,
                true,
                false
            ],
            'Authentication: SessionExpired' => [
                SessionExpiredMaatifyException::class,
                ErrorCategoryEnum::AUTHENTICATION,
                401,
                ErrorCodeEnum::SESSION_EXPIRED,
                true,
                false
            ],
            'Authentication: Unauthorized' => [
                UnauthorizedMaatifyException::class,
                ErrorCategoryEnum::AUTHENTICATION,
                401,
                ErrorCodeEnum::UNAUTHORIZED,
                true,
                false
            ],
            'Authorization: Generic' => [
                get_class(new class extends AuthorizationMaatifyException {
                    protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::FORBIDDEN; }
                }),
                ErrorCategoryEnum::AUTHORIZATION,
                403,
                ErrorCodeEnum::FORBIDDEN,
                true,
                false
            ],
            'Authorization: Forbidden' => [
                ForbiddenMaatifyException::class,
                ErrorCategoryEnum::AUTHORIZATION,
                403,
                ErrorCodeEnum::FORBIDDEN,
                true,
                false
            ],
            'BusinessRule: Generic' => [
                get_class(new class extends BusinessRuleMaatifyException {
                }),
                ErrorCategoryEnum::BUSINESS_RULE,
                422,
                ErrorCodeEnum::BUSINESS_RULE_VIOLATION,
                true,
                false
            ],
            'Conflict: Generic' => [
                get_class(new class extends ConflictMaatifyException {
                    protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::CONFLICT; }
                }),
                ErrorCategoryEnum::CONFLICT,
                409,
                ErrorCodeEnum::CONFLICT,
                true,
                false
            ],
            'Conflict: GenericConflict' => [
                GenericConflictMaatifyException::class,
                ErrorCategoryEnum::CONFLICT,
                409,
                ErrorCodeEnum::CONFLICT,
                true,
                false
            ],
            'NotFound: Generic' => [
                get_class(new class extends NotFoundMaatifyException {
                    protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::RESOURCE_NOT_FOUND; }
                }),
                ErrorCategoryEnum::NOT_FOUND,
                404,
                ErrorCodeEnum::RESOURCE_NOT_FOUND,
                true,
                false
            ],
            'NotFound: ResourceNotFound' => [
                ResourceNotFoundMaatifyException::class,
                ErrorCategoryEnum::NOT_FOUND,
                404,
                ErrorCodeEnum::RESOURCE_NOT_FOUND,
                true,
                false
            ],
            'RateLimit: Generic' => [
                get_class(new class extends RateLimitMaatifyException {
                    protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::TOO_MANY_REQUESTS; }
                }),
                ErrorCategoryEnum::RATE_LIMIT,
                429,
                ErrorCodeEnum::TOO_MANY_REQUESTS,
                true,
                true // RateLimit is retryable by default
            ],
            'RateLimit: TooManyRequests' => [
                TooManyRequestsMaatifyException::class,
                ErrorCategoryEnum::RATE_LIMIT,
                429,
                ErrorCodeEnum::TOO_MANY_REQUESTS,
                true,
                true
            ],
            'Security: Generic' => [
                get_class(new class extends SecurityMaatifyException {
                    protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::FORBIDDEN; }
                }),
                ErrorCategoryEnum::SECURITY,
                403,
                ErrorCodeEnum::FORBIDDEN,
                true,
                false
            ],
            'Unsupported: Generic' => [
                get_class(new class extends UnsupportedMaatifyException {
                    protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::UNSUPPORTED_OPERATION; }
                }),
                ErrorCategoryEnum::UNSUPPORTED,
                409,
                ErrorCodeEnum::UNSUPPORTED_OPERATION,
                true,
                false
            ],
            'Unsupported: UnsupportedOperation' => [
                UnsupportedOperationMaatifyException::class,
                ErrorCategoryEnum::UNSUPPORTED,
                409,
                ErrorCodeEnum::UNSUPPORTED_OPERATION,
                true,
                false
            ],
        ];
    }

    /**
     * @param class-string<MaatifyException> $exceptionClass
     */
    #[DataProvider('exceptionProvider')]
    public function testExceptionDefaults(
        string $exceptionClass,
        ErrorCategoryInterface $expectedCategory,
        int $expectedStatus,
        ?ErrorCodeInterface $expectedCode,
        bool $expectedSafe,
        bool $expectedRetryable
    ): void {
        $exception = new $exceptionClass();

        $this->assertSame($expectedCategory, $exception->getCategory());
        $this->assertSame($expectedStatus, $exception->getHttpStatus());
        if ($expectedCode !== null) {
            $this->assertSame($expectedCode, $exception->getErrorCode());
        }
        $this->assertSame($expectedSafe, $exception->isSafe());
        $this->assertSame($expectedRetryable, $exception->isRetryable());
    }

    public function testImmutability(): void
    {
        $exception = new ResourceNotFoundMaatifyException();
        $category1 = $exception->getCategory();
        $category2 = $exception->getCategory();

        $this->assertSame($category1, $category2);
    }
}
