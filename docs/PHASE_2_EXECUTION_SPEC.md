# Maatify Exceptions — Phase 2

## Execution Spec (Locked)

> Status: **LOCKED**
> Scope: **Additive only** — No breaking changes to v1.0 public contracts.

---

## 1) Goals

Phase 2 expands `maatify/exceptions` from an exception engine into a complete, framework-agnostic error handling layer by adding:

1. **Normalization Layer**: `Throwable → NormalizedError`
2. **Formatting Layer**: `NormalizedError → response body`
3. **Response Model**: status + headers + contentType + body
4. **RFC7807 adapter**: `application/problem+json` format option

All outputs must remain deterministic and safe by default.

---

## 2) Non-Negotiable Constraints

### 2.1 Compatibility

* No changes to existing v1 public contracts.
* Additive namespaces/files only.
* Existing behavior is preserved.

### 2.2 Determinism

* Same Throwable + Same Context → identical output.
* No timestamps.
* No randomness.
* No implicit environment coupling.
* No stack traces in default outputs.

### 2.3 Framework agnosticism

* No PSR-7 / Symfony / Laravel response objects.
* No Request objects.
* No `$_SERVER`, no globals.

### 2.4 Safety

* `message` is always safe to show to clients.
* No original external exception messages are exposed.
* `meta` must not contain secrets.

### 2.5 Quality

* Maintain ≥ 100% coverage (classes/methods/lines).
* Zero warnings.

---

## 3) Locked Contracts

### 3.1 ErrorEnvelope (Default JSON output)

Root keys allowed in v1:

* `error` (required)
* `trace_id` (optional)

```json
{
  "error": {
    "code": "VALIDATION_FAILED",
    "message": "Invalid input",
    "status": 400,
    "category": "validation",
    "retryable": false,
    "safe": true,
    "meta": {}
  },
  "trace_id": "abc123"
}
```

#### Rules

* `error.status` must match HTTP status.
* `meta` is always present and always an object (empty `{}` allowed).
* `trace_id` is outside `error`.

---

### 3.2 RFC7807 (Problem Details output)

```json
{
  "type": "https://maatify.dev/problems/validation",
  "title": "Validation failed",
  "status": 400,
  "detail": "Invalid input",
  "instance": "/request-uri",
  "extensions": {
    "code": "VALIDATION_FAILED",
    "category": "validation",
    "retryable": false,
    "safe": true,
    "meta": {}
  }
}
```

#### Rules

* RFC7807 required fields used: `type`, `title`, `status`, `detail`, `instance`
* All non-standard fields go under `extensions`
* No information loss versus Default JSON

---

### 3.3 NormalizedError (Immutable VO)

Fields (all required):

| Field     | Type   |
|-----------|--------|
| code      | string |
| message   | string |
| status    | int    |
| category  | string |
| retryable | bool   |
| safe      | bool   |
| meta      | array  |

Rules:

* `code` = **UPPERCASE_SNAKE_CASE**
* `category` = lowercase (immutable category identity)
* `meta` always present (empty allowed)
* **No Throwable reference** (pure VO)

---

### 3.4 ErrorContext (Minimal immutable VO)

Fields:

| Field    | Type    | Default |
|----------|---------|---------|
| traceId  | ?string | null    |
| instance | ?string | null    |
| debug    | bool    | false   |

Rules:

* no framework coupling
* determinism preserved

---

### 3.5 ErrorResponseModel (Immutable VO)

Fields:

| Field       | Type                 |
|-------------|----------------------|
| status      | int                  |
| headers     | array<string,string> |
| contentType | string               |
| body        | array                |

Rules:

* `status` equals `body`’s status representation.
* Default JSON contentType: `application/json; charset=utf-8`
* RFC7807 contentType: `application/problem+json; charset=utf-8`
* headers are deterministic; default empty.

---

## 4) Throwable Mapping (Locked)

### 4.1 If Throwable is `MaatifyException`

Map directly from its domain data into `NormalizedError` fields:

* code, message, status, category, retryable, safe, meta

### 4.2 If Throwable is NOT `MaatifyException`

Return deterministic fallback:

* `code`: `INTERNAL_ERROR`
* `message`: `An unexpected error occurred.`
* `status`: `500`
* `category`: `internal`
* `retryable`: `false`
* `safe`: `true`
* `meta`: `{}`

Rules:

* Never expose original external throwable message.
* No stack trace.

---

## 5) Extension Points (Locked)

### 5.1 Throwable mapping

Introduce:

* `ThrowableToErrorInterface`

Default implementation:

* `DefaultThrowableToError`

Must support injection/replacement.

### 5.2 Formatting

Introduce:

* `FormatterInterface`

Must support multiple formatters:

* Default JSON envelope formatter
* RFC7807 problem details formatter

ErrorSerializer must accept formatter via constructor injection (no internal branching).

---

## 6) Responsibilities (Locked)

### 6.1 ThrowableToErrorInterface

* Input: `Throwable`
* Output: `NormalizedError`

### 6.2 FormatterInterface

* Input: `NormalizedError`, `ErrorContext`
* Output: `ErrorResponseModel`

### 6.3 ErrorSerializer

* Orchestrator (no policy decisions):

    * uses `ThrowableToErrorInterface` to normalize
    * delegates to `FormatterInterface` to format
* Must return `ErrorResponseModel`

---

## 7) File / Namespace Plan (Additive Only)

> Namespaces are indicative; keep consistency with existing library conventions.

### 7.1 Application Error

* `src/Application/Error/NormalizedError.php`
* `src/Application/Error/ErrorContext.php`
* `src/Application/Error/ErrorResponseModel.php`
* `src/Application/Error/ThrowableToErrorInterface.php`
* `src/Application/Error/DefaultThrowableToError.php`
* `src/Application/Error/ErrorSerializer.php`

### 7.2 Application Formatting

* `src/Application/Format/FormatterInterface.php`
* `src/Application/Format/JsonErrorFormatter.php`
* `src/Application/Format/ProblemDetailsFormatter.php`

---

## 8) Formatter Rules (Locked)

### 8.1 JsonErrorFormatter

* Produces Default ErrorEnvelope body
* `trace_id` root key included only when `context.traceId !== null`
* contentType: `application/json; charset=utf-8`
* headers: default `{}`

### 8.2 ProblemDetailsFormatter

* Produces RFC7807 body
* `instance` uses `context.instance` if provided else omit or null (must be deterministic; prefer omitting when null)
* `extensions` always present and includes:

    * code, category, retryable, safe, meta
* `type`:

    * `https://maatify.dev/problems/{category}`
* `title`:

    * Deterministic mapping table by category
    * Minimum required mapping:

        * validation → `Validation failed`
        * authentication → `Authentication required`
        * authorization → `Permission denied`
        * conflict → `Conflict`
        * internal → `Internal error`
* contentType: `application/problem+json; charset=utf-8`
* headers: default `{}`

---

## 9) Tests (Mandatory)

### 9.1 Golden determinism tests

* Same NormalizedError + same context => same output array exactly

### 9.2 Mapping tests

* MaatifyException maps correctly to NormalizedError
* External Throwable maps to INTERNAL_ERROR fallback

### 9.3 Formatter tests

* Json formatter output matches contract (presence of meta, status, trace_id behavior)
* RFC7807 formatter output matches contract (type/title/status/detail/instance/extensions)

### 9.4 Serializer orchestration tests

* ErrorSerializer returns `ErrorResponseModel`
* Injection works (custom mapper + custom formatter)

---

## 10) Done Criteria

* All new classes implemented under additive namespaces.
* 100% coverage for new code.
* No changes to v1 public behavior.
* Outputs match locked contracts exactly.

---

## 11) Implementation Order (Locked)

1. `NormalizedError`
2. `ErrorContext`
3. `ErrorResponseModel`
4. `ThrowableToErrorInterface`
5. `DefaultThrowableToError`
6. `FormatterInterface`
7. `JsonErrorFormatter`
8. `ProblemDetailsFormatter`
9. `ErrorSerializer`
10. Tests + golden assertions

---
