<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Application\Error;

use Maatify\Exceptions\Application\Format\FormatterInterface;
use Throwable;

final readonly class ErrorSerializer
{
    public function __construct(
        private ThrowableToErrorInterface $mapper,
        private FormatterInterface $formatter
    ) {
    }

    public function serialize(Throwable $t, ?ErrorContext $context = null): ErrorResponseModel
    {
        $context = $context ?? new ErrorContext();
        $normalized = $this->mapper->map($t);

        return $this->formatter->format($normalized, $context);
    }
}
