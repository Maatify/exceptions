<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Application\Error;

use Maatify\Exceptions\Application\Format\FormatterInterface;
use Throwable;

final class ErrorSerializer
{
    private ThrowableToErrorInterface $mapper;
    private FormatterInterface $formatter;

    public function __construct(
        ThrowableToErrorInterface $mapper,
        FormatterInterface $formatter
    ) {
        $this->mapper = $mapper;
        $this->formatter = $formatter;
    }

    public function serialize(Throwable $t, ?ErrorContext $context = null): ErrorResponseModel
    {
        $context = $context ?? new ErrorContext();
        $normalized = $this->mapper->map($t);

        return $this->formatter->format($normalized, $context);
    }
}
