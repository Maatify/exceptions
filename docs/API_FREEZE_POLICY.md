# Maatify Exceptions

## API Freeze Policy (1.x Series)

> Status: ACTIVE
> Applies to: `maatify/exceptions` 1.x
> Effective from: v1.0.0 and all subsequent 1.x releases

---

# 1️⃣ Purpose

This document defines the stability and compatibility guarantees of the public API surface of:

> `maatify/exceptions`

The goal is to ensure long-term predictability, deterministic behavior, and safe ecosystem adoption.

---

# 2️⃣ Versioning Model

This library follows **Semantic Versioning (SemVer)**.

* `MAJOR` → Breaking changes
* `MINOR` → Additive, backward-compatible features
* `PATCH` → Bug fixes only

---

# 3️⃣ 1.x Stability Guarantee

For all versions `>=1.0.0 <2.0.0`:

### ❌ No Breaking Changes Allowed

The following are frozen:

* Public class names
* Public method signatures
* Constructor signatures
* Public return types
* Exception category identities
* HTTP family guard behavior
* Escalation logic behavior
* NormalizedError structure
* ErrorEnvelope JSON structure
* RFC7807 mapping structure
* ErrorResponseModel structure
* ErrorContext structure

---

# 4️⃣ Public Surface Definition

The following namespaces constitute the public API:

* `Domain\` (Exception Engine)
* `Application\Error\` (Normalization Layer)
* `Application\Format\` (Formatting Layer)

Everything else is considered internal unless explicitly documented.

---

# 5️⃣ What Is Allowed in 1.x

The following changes are allowed:

### ✅ Add new classes

As long as they do not alter existing behavior.

### ✅ Add new interfaces

Without modifying existing signatures.

### ✅ Add optional constructor parameters

Only if:

* Default value preserves behavior
* No ambiguity introduced

### ✅ Add new formatters

Without changing existing formatter behavior.

### ✅ Add new exception categories

If:

* They do not alter existing category behavior
* They respect HTTP family constraints

---

# 6️⃣ What Is Strictly Forbidden in 1.x

### ❌ Changing JSON envelope structure

The following keys are locked:

Default JSON:

* root.error
* root.trace_id
* error.code
* error.message
* error.status
* error.category
* error.retryable
* error.safe
* error.meta

RFC7807:

* type
* title
* status
* detail
* instance
* extensions structure

---

### ❌ Changing code naming convention

* Must remain UPPERCASE_SNAKE_CASE
* No numeric codes introduced
* No class-name-derived codes

---

### ❌ Removing or renaming fields

Even if technically “optional”, removal is breaking.

---

### ❌ Changing fallback behavior

External throwable fallback must remain:

```
code = INTERNAL_ERROR
status = 500
category = internal
retryable = false
safe = true
meta = {}
```

---

### ❌ Adding implicit behavior

* No automatic timestamps
* No implicit environment detection
* No debug leaks
* No global state coupling

---

# 7️⃣ Determinism Lock

The following are part of the frozen behavior:

* Same Throwable + same Context → identical output
* No randomness
* No hidden data injection
* No stack trace exposure

Breaking determinism = breaking change.

---

## 7.1 Determinism Boundary & Extension Responsibility

The library guarantees deterministic behavior for all **shipped components**, including:

* DefaultThrowableToError
* JsonErrorFormatter
* ProblemDetailsFormatter
* ErrorSerializer
* All Value Objects

Determinism means:

* Same Throwable + Same Context → identical output
* No randomness
* No timestamps
* No environment-based branching
* No stack trace exposure

However, the following are **outside the deterministic guarantee of the library**:

* Custom implementations of `ThrowableToErrorInterface`
* Custom implementations of `FormatterInterface`
* Application-provided `meta` data

The library guarantees deterministic **transformation of input**.
It does not guarantee deterministic **input provided by the application**.

Custom extensions must preserve:

* Safety
* Determinism
* JSON contract stability

Failure to do so constitutes misuse of the extension surface, not a defect in the kernel.

---

# 8️⃣ Extension Rules

All extension mechanisms must:

* Preserve determinism
* Preserve output contract
* Not mutate existing structures
* Not override core fallback logic unless explicitly injected

---

# 9️⃣ Deprecation Policy

If deprecation becomes necessary:

1. Mark with `@deprecated`
2. Keep functionality intact for full 1.x lifecycle
3. Document migration path
4. Remove only in 2.0

---

# 🔟 2.0 Eligibility

The following may justify a 2.0 release:

* Changing JSON envelope shape
* Introducing numeric error codes
* Modifying fallback behavior
* Changing category model semantics
* Altering status resolution logic

---

# 🔚 Final Authority

This document is binding for all 1.x releases.

If any ambiguity exists:

> Determinism and stability take precedence over convenience.

---
