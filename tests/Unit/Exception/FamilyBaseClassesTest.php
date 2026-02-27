<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Tests\Unit\Exception;

use Maatify\Exceptions\Contracts\ErrorCodeInterface;
use Maatify\Exceptions\Enum\ErrorCategoryEnum;
use Maatify\Exceptions\Enum\ErrorCodeEnum;
use Maatify\Exceptions\Exception\Authentication\AuthenticationMaatifyException;
use Maatify\Exceptions\Exception\Authorization\AuthorizationMaatifyException;
use Maatify\Exceptions\Exception\BusinessRule\BusinessRuleMaatifyException;
use Maatify\Exceptions\Exception\Conflict\ConflictMaatifyException;
use Maatify\Exceptions\Exception\NotFound\NotFoundMaatifyException;
use Maatify\Exceptions\Exception\RateLimit\RateLimitMaatifyException;
use Maatify\Exceptions\Exception\Security\SecurityMaatifyException;
use Maatify\Exceptions\Exception\System\SystemMaatifyException;
use Maatify\Exceptions\Exception\Unsupported\UnsupportedMaatifyException;
use Maatify\Exceptions\Exception\Validation\ValidationMaatifyException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthenticationMaatifyException::class)]
#[CoversClass(AuthorizationMaatifyException::class)]
#[CoversClass(BusinessRuleMaatifyException::class)]
#[CoversClass(ConflictMaatifyException::class)]
#[CoversClass(NotFoundMaatifyException::class)]
#[CoversClass(RateLimitMaatifyException::class)]
#[CoversClass(SecurityMaatifyException::class)]
#[CoversClass(SystemMaatifyException::class)]
#[CoversClass(UnsupportedMaatifyException::class)]
#[CoversClass(ValidationMaatifyException::class)]
final class FamilyBaseClassesTest extends TestCase
{
    public function testSystemMaatifyExceptionDefaults(): void
    {
        $exception = new class extends SystemMaatifyException {
            protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::MAATIFY_ERROR; }
        };
        $this->assertSame(ErrorCategoryEnum::SYSTEM, $exception->getCategory());
        $this->assertSame(500, $exception->getHttpStatus());
        $this->assertFalse($exception->isSafe());
    }

    public function testValidationMaatifyExceptionDefaults(): void
    {
        $exception = new class extends ValidationMaatifyException {
            protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::INVALID_ARGUMENT; }
        };
        $this->assertSame(ErrorCategoryEnum::VALIDATION, $exception->getCategory());
        $this->assertSame(400, $exception->getHttpStatus());
        $this->assertTrue($exception->isSafe());
    }

    public function testAuthenticationMaatifyExceptionDefaults(): void
    {
        $exception = new class extends AuthenticationMaatifyException {
            protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::UNAUTHORIZED; }
        };
        $this->assertSame(ErrorCategoryEnum::AUTHENTICATION, $exception->getCategory());
        $this->assertSame(401, $exception->getHttpStatus());
        $this->assertTrue($exception->isSafe());
    }

    public function testAuthorizationMaatifyExceptionDefaults(): void
    {
        $exception = new class extends AuthorizationMaatifyException {
            protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::FORBIDDEN; }
        };
        $this->assertSame(ErrorCategoryEnum::AUTHORIZATION, $exception->getCategory());
        $this->assertSame(403, $exception->getHttpStatus());
        $this->assertTrue($exception->isSafe());
    }

    public function testBusinessRuleMaatifyExceptionDefaults(): void
    {
        // BusinessRule implements defaultErrorCode directly in abstract?
        // Let's check source later. For now assume it doesn't hurt to not implement it if it does,
        // or check if it is abstract.
        // Source check showed: protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::BUSINESS_RULE_VIOLATION; }
        // So we don't need to implement it.

        $exception = new class extends BusinessRuleMaatifyException {};
        $this->assertSame(ErrorCategoryEnum::BUSINESS_RULE, $exception->getCategory());
        $this->assertSame(422, $exception->getHttpStatus());
        $this->assertTrue($exception->isSafe());
        $this->assertSame(ErrorCodeEnum::BUSINESS_RULE_VIOLATION, $exception->getErrorCode());
    }

    public function testConflictMaatifyExceptionDefaults(): void
    {
        $exception = new class extends ConflictMaatifyException {
            protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::CONFLICT; }
        };
        $this->assertSame(ErrorCategoryEnum::CONFLICT, $exception->getCategory());
        $this->assertSame(409, $exception->getHttpStatus());
        $this->assertTrue($exception->isSafe());
    }

    public function testNotFoundMaatifyExceptionDefaults(): void
    {
        $exception = new class extends NotFoundMaatifyException {
            protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::RESOURCE_NOT_FOUND; }
        };
        $this->assertSame(ErrorCategoryEnum::NOT_FOUND, $exception->getCategory());
        $this->assertSame(404, $exception->getHttpStatus());
        $this->assertTrue($exception->isSafe());
    }

    public function testRateLimitMaatifyExceptionDefaults(): void
    {
        $exception = new class extends RateLimitMaatifyException {
            protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::TOO_MANY_REQUESTS; }
        };
        $this->assertSame(ErrorCategoryEnum::RATE_LIMIT, $exception->getCategory());
        $this->assertSame(429, $exception->getHttpStatus());
        $this->assertTrue($exception->isSafe());
        $this->assertTrue($exception->isRetryable());
    }

    public function testUnsupportedMaatifyExceptionDefaults(): void
    {
        $exception = new class extends UnsupportedMaatifyException {
            protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::UNSUPPORTED_OPERATION; }
        };
        $this->assertSame(ErrorCategoryEnum::UNSUPPORTED, $exception->getCategory());
        $this->assertSame(409, $exception->getHttpStatus());
        $this->assertTrue($exception->isSafe());
    }

    public function testSecurityMaatifyExceptionDefaults(): void
    {
        $exception = new class extends SecurityMaatifyException {
            protected function defaultErrorCode(): ErrorCodeInterface { return ErrorCodeEnum::FORBIDDEN; }
        };
        $this->assertSame(ErrorCategoryEnum::SECURITY, $exception->getCategory());
        $this->assertSame(403, $exception->getHttpStatus());
        $this->assertTrue($exception->isSafe());
    }
}
