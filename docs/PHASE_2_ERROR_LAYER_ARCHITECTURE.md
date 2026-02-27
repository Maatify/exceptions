# Maatify Exceptions — Phase 2

## Error Handling Infrastructure Architecture

---

## 1️⃣ Overview

Phase 2 transforms:

> `maatify/exceptions`

From:

> Deterministic Exception Engine

To:

> Framework-Agnostic Error Handling Infrastructure Layer

This phase introduces a complete, extensible, deterministic error serialization layer
without modifying any v1.0 public contracts.

---

## 2️⃣ Architectural Principles

### ✅ No Breaking Changes

* v1 public contracts remain stable.
* All additions are additive.
* No behavioral change to existing exception engine.

### ✅ Deterministic Behavior

* Same Throwable + Same Context → Same Output (byte-for-byte).
* No timestamps.
* No randomness.
* No implicit environment coupling.

### ✅ Framework-Agnostic

* No Symfony/Laravel/PSR-7 dependency.
* No Request object inside the library.
* No global state usage.

### ✅ Clean Separation of Layers

* Domain Layer (existing)
* Normalization Layer (new)
* Formatting Layer (new)
* Response Model (new)

---

## 3️⃣ Complete Error Pipeline

```
Throwable
   ↓
ThrowableToErrorInterface
   ↓
NormalizedError (Immutable VO)
   ↓
FormatterInterface
   ↓
ErrorResponseModel (Immutable VO)
```

---

# 4️⃣ Core Contracts (Locked)

---

## 4.1 NormalizedError

Immutable Value Object.

### Fields:

| Field     | Type   | Required |
|-----------|--------|----------|
| code      | string | ✅        |
| message   | string | ✅        |
| status    | int    | ✅        |
| category  | string | ✅        |
| retryable | bool   | ✅        |
| safe      | bool   | ✅        |
| meta      | array  | ✅        |

### Rules

* `code` = UPPERCASE_SNAKE_CASE semantic code.
* `category` = lowercase immutable category.
* `meta` = always an array (even if empty).
* No Throwable reference.
* No stack trace.
* No previous exception.
* Pure value object.

---

## 4.2 Default JSON Error Envelope

```
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

### Rules

* `error` always present.
* `status` must match HTTP status header.
* `meta` always present (object).
* `trace_id` is outside `error`.
* No additional root keys in v1.

---

## 4.3 RFC7807 Problem Details Format

```
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

### Rules

* Fully RFC7807 compliant.
* No information loss.
* All additional fields go inside `extensions`.
* Deterministic mapping.

---

## 4.4 ErrorResponseModel

Immutable Value Object.

### Fields

| Field       | Type                 | Required |
|-------------|----------------------|----------|
| status      | int                  | ✅        |
| headers     | array<string,string> | ✅        |
| contentType | string               | ✅        |
| body        | array                | ✅        |

### Rules

* `status` must equal body.status.
* Default contentType:

  ```
  application/json; charset=utf-8
  ```
* For RFC7807:

  ```
  application/problem+json; charset=utf-8
  ```
* No framework-specific response object.

---

## 4.5 ErrorContext

Minimal immutable context.

### Fields

| Field    | Type    | Required          |
|----------|---------|-------------------|
| traceId  | ?string | ❌                 |
| instance | ?string | ❌                 |
| debug    | bool    | ❌ (default false) |

### Rules

* No Request object.
* No globals.
* No environment injection.
* debug=false → no debug leakage.

---

# 5️⃣ Throwable Mapping Rules

---

## 5.1 If Throwable is MaatifyException

Direct extraction:

* code
* message
* status
* category
* retryable
* safe
* meta

Deterministic.

---

## 5.2 If Throwable is External

Fallback:

```
code = INTERNAL_ERROR
message = "An unexpected error occurred."
status = 500
category = "internal"
retryable = false
safe = true
meta = {}
```

### Rules

* Do not expose original message.
* Do not expose stack trace.
* Deterministic.

---

# 6️⃣ Extension Points

* ThrowableToErrorInterface
* FormatterInterface
* Custom Throwable Mapper injection
* Future Meta Providers
* Future Code Strategy

All extensions must preserve determinism.

---

# 7️⃣ Determinism Laws

1. No timestamps.
2. No randomness.
3. No hidden environment behavior.
4. Pure mapping functions.
5. Same input → identical output.

---

# 8️⃣ Security Laws

1. `message` must always be safe.
2. `meta` must not contain sensitive data.
3. No stack traces in default formatter.
4. No exception class leakage.

---

# 9️⃣ Versioning & Stability

* All contracts introduced in Phase 2 are stable for 1.x.
* Breaking changes allowed only in 2.0.
* Envelope structure is frozen for 1.x.

---

# 🔚 Final Status

Phase 2 Architecture is fully locked and ready for implementation.
