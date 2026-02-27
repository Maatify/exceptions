<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Application\Error;

use Throwable;

interface ThrowableToErrorInterface
{
    /**
     * Maps any Throwable to a NormalizedError VO.
     * Must be deterministic.
     */
    public function map(Throwable $t): NormalizedError;
}
