# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - Error Serialization Layer

### Summary
Introduced a complete, deterministic Error Serialization Layer (Phase 2).
This update adds the ability to transform any `Throwable` into a standardized, safe, and immutable JSON response (Default or RFC7807) without modifying existing exception logic.

### Added
- **Error Serialization Engine:**
  - `ErrorSerializer`: Main orchestrator for converting exceptions to responses.
  - `ThrowableToErrorInterface`: Contract for normalizing exceptions.
  - `FormatterInterface`: Contract for formatting responses.
  - `DefaultThrowableToError`: Strict fallback mechanism for external exceptions.

- **Immutable Value Objects:**
  - `NormalizedError`: Internal representation of a sanitized error.
  - `ErrorContext`: Contextual data (trace ID, debug mode) for serialization.
  - `ErrorResponseModel`: Final output abstraction (status, headers, body).

- **Standard Formatters:**
  - `JsonErrorFormatter`: Produces the standard Maatify JSON envelope.
  - `ProblemDetailsFormatter`: Produces RFC7807 compliant "Problem Details" JSON.

### Guarantees
- **Determinism:** The same input `Throwable` + `ErrorContext` will always produce the exact same byte-for-byte output.
- **Safety:** External exception messages are never exposed to the client; they are masked by a generic "Internal Server Error" unless explicitly mapped.
- **Zero Breaking Changes:** All new components are additive. The existing 1.0 exception engine remains untouched.

## [1.0.0] - Initial Stable Release

### Summary
First stable release of the policy-driven exception taxonomy system.

### Added
- Core `ApiAwareExceptionInterface` contract.
- Base `MaatifyException` class with guarded taxonomy logic.
- `ErrorCategoryEnum` and `ErrorCodeEnum` with strict validation.
- Full suite of standard exception families:
  - `Authentication`
  - `Authorization`
  - `BusinessRule`
  - `Conflict`
  - `NotFound`
  - `RateLimit`
  - `System`
  - `Unsupported`
  - `Validation`
- Automatic severity escalation system in `MaatifyException`.
- `SessionExpiredMaatifyException` under Authentication family.
- `BusinessRuleMaatifyException` explicitly defaults to `BUSINESS_RULE_VIOLATION`.

### Security
- Implemented category immutability to prevent taxonomy drift.
- Enforced strict HTTP status family matching (4xx vs 5xx).
- Enforced deterministic severity escalation (no downgrades allowed).
### Guarantees
- Public API surface frozen.
- Constructor signature considered stable.
- Escalation algorithm considered part of the public contract.
