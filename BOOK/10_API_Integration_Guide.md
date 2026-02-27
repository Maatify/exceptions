# API Integration Guide

This guide explains how to integrate `maatify/exceptions` with your API layer.

## 1. Global Exception Handler

Every modern PHP application should have a global exception handler.
We recommend using the built-in `ErrorSerializer` for this purpose.

### Middleware Approach

If you are using PSR-15 Middleware:

```php
use Maatify\Exceptions\Application\Error\ErrorSerializer;
use Maatify\Exceptions\Application\Error\ErrorContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ErrorHandlerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ErrorSerializer $serializer,
        private ResponseFactoryInterface $responseFactory
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            // 1. Create context (e.g., attach trace ID from request)
            $context = new ErrorContext(
                traceId: $request->getAttribute('trace_id')
            );
            
            // 2. Serialize to Response Model
            $errorResponse = $this->serializer->serialize($e, $context);
            
            // 3. Convert to PSR-7 Response
            $response = $this->responseFactory->createResponse($errorResponse->getStatus());
            
            foreach ($errorResponse->getHeaders() as $name => $value) {
                $response = $response->withHeader($name, $value);
            }
            
            $response = $response->withHeader('Content-Type', $errorResponse->getContentType());
            $response->getBody()->write(json_encode($errorResponse->getBody()));
            
            return $response;
        }
    }
}
```

## 2. JSON Format

The library supports two output formats, controlled by the `FormatterInterface` you inject into the `ErrorSerializer`.

### Standard JSON (`JsonErrorFormatter`)

```json
{
    "error": {
        "code": "INVALID_ARGUMENT",
        "message": "Email field is required",
        "status": 400,
        "category": "validation",
        "retryable": false,
        "safe": true,
        "meta": {
            "field": "email"
        }
    },
    "trace_id": "req_123"
}
```

### RFC7807 Problem Details (`ProblemDetailsFormatter`)

```json
{
    "type": "https://maatify.dev/problems/validation",
    "title": "Validation failed",
    "status": 400,
    "detail": "Email field is required",
    "extensions": {
        "code": "INVALID_ARGUMENT",
        "category": "validation",
        "retryable": false,
        "safe": true,
        "meta": {
            "field": "email"
        }
    }
}
```

## 3. Logging

Always log the full exception stack trace, especially for System errors.

```php
$logger->error($e->getMessage(), [
    'exception' => $e,
    'category' => $e instanceof ApiAwareExceptionInterface ? $e->getCategory()->value : 'internal',
    'code' => $e instanceof ApiAwareExceptionInterface ? $e->getErrorCode()->value : 'INTERNAL_ERROR'
]);
```
