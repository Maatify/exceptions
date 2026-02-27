<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Application\Error;

use Maatify\Exceptions\Exception\MaatifyException;
use Throwable;

final class DefaultThrowableToError implements ThrowableToErrorInterface
{
    public function map(Throwable $t): NormalizedError
    {
        if ($t instanceof MaatifyException) {
            return new NormalizedError(
                $t->getErrorCode()->getValue(),
                $t->getMessage(),
                $t->getHttpStatus(),
                $t->getCategory()->getValue(),
                $t->isRetryable(),
                $t->isSafe(),
                $t->getMeta()
            );
        }

        // Fallback for external exceptions
        return new NormalizedError(
            'INTERNAL_ERROR',
            'An unexpected error occurred.',
            500,
            'internal',
            false,
            true,
            []
        );
    }
}
