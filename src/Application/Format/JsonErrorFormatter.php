<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Application\Format;

use Maatify\Exceptions\Application\Error\ErrorContext;
use Maatify\Exceptions\Application\Error\ErrorResponseModel;
use Maatify\Exceptions\Application\Error\NormalizedError;

final class JsonErrorFormatter implements FormatterInterface
{
    public function format(NormalizedError $error, ErrorContext $context): ErrorResponseModel
    {
        $body = [
            'error' => [
                'code' => $error->getCode(),
                'message' => $error->getMessage(),
                'status' => $error->getStatus(),
                'category' => $error->getCategory(),
                'retryable' => $error->isRetryable(),
                'safe' => $error->isSafe(),
                'meta' => $error->getMeta(),
            ],
        ];

        if ($context->getTraceId() !== null) {
            $body['trace_id'] = $context->getTraceId();
        }

        return new ErrorResponseModel(
            $error->getStatus(),
            [],
            'application/json; charset=utf-8',
            $body
        );
    }
}
