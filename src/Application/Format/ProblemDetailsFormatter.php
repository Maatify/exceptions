<?php

declare(strict_types=1);

namespace Maatify\Exceptions\Application\Format;

use Maatify\Exceptions\Application\Error\ErrorContext;
use Maatify\Exceptions\Application\Error\ErrorResponseModel;
use Maatify\Exceptions\Application\Error\NormalizedError;

final class ProblemDetailsFormatter implements FormatterInterface
{
    private const CATEGORY_TITLES = [
        'validation' => 'Validation failed',
        'authentication' => 'Authentication required',
        'authorization' => 'Permission denied',
        'conflict' => 'Conflict',
        'internal' => 'Internal error',
    ];

    public function format(NormalizedError $error, ErrorContext $context): ErrorResponseModel
    {
        $category = $error->getCategory();
        $title = self::CATEGORY_TITLES[$category] ?? ucfirst($category);

        $body = [
            'type' => "https://maatify.dev/problems/{$category}",
            'title' => $title,
            'status' => $error->getStatus(),
            'detail' => $error->getMessage(),
        ];

        if ($context->getInstance() !== null) {
            $body['instance'] = $context->getInstance();
        }

        $body['extensions'] = [
            'code' => $error->getCode(),
            'category' => $category,
            'retryable' => $error->isRetryable(),
            'safe' => $error->isSafe(),
            'meta' => $error->getMeta(),
        ];

        return new ErrorResponseModel(
            $error->getStatus(),
            [],
            'application/problem+json; charset=utf-8',
            $body
        );
    }
}
