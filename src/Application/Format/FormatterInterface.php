<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Application\Format;

use Maatify\Exceptions\Application\Error\ErrorContext;
use Maatify\Exceptions\Application\Error\ErrorResponseModel;
use Maatify\Exceptions\Application\Error\NormalizedError;

interface FormatterInterface
{
    /**
     * Formats a normalized error into a response model.
     * Must be deterministic.
     */
    public function format(NormalizedError $error, ErrorContext $context): ErrorResponseModel;
}
