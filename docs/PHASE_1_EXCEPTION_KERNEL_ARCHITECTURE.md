# Maatify Exceptions — Phase 1

## Deterministic Exception Kernel Architecture (v1.0.0)

---

## 1️⃣ Overview

Phase 1 establishes the foundational core of:

> `maatify/exceptions`

It delivers a deterministic, policy-driven, framework-agnostic exception engine
ready for production use in modern PHP 8.2+ applications.

---

## 2️⃣ Design Goal

Transform PHP exception handling from:

> Ad-hoc throwable usage

Into:

> Deterministic, structured, policy-aware exception kernel

---

## 3️⃣ Core Characteristics

* ✅ PHP 8.2+
* ✅ PHPUnit 11
* ✅ 100% Code Coverage (Classes / Methods / Lines)
* ✅ Zero warnings
* ✅ Immutable Category Model
* ✅ Deterministic Escalation Engine
* ✅ Strict HTTP Family Guard
* ✅ Policy-Driven Validation
* ✅ Global Policy Injection Support
* ✅ Stable Public Contracts

---

# 4️⃣ Architectural Layers (Phase 1)

```text
Application Code
      ↓
MaatifyException
      ↓
Category Model
      ↓
Policy Engine
      ↓
Escalation Engine
      ↓
HTTP Family Guard
```

---

# 5️⃣ Core Components

---

## 5.1 MaatifyException

Base exception class for all structured exceptions.

### Guarantees:

* Deterministic behavior
* Controlled status resolution
* Structured metadata support
* Safe message handling
* No implicit runtime behavior

---

## 5.2 Immutable Category Model

Categories represent semantic error domains.

### Properties:

* Immutable
* Lowercase string identity
* Deterministic
* Stable across versions

### Examples:

* validation
* authentication
* authorization
* conflict
* internal

---

## 5.3 Deterministic Escalation Engine

Responsible for:

* Escalating status codes based on category
* Enforcing policy constraints
* Guaranteeing predictable output

### Rules:

* No randomness
* No dynamic behavior
* Fully test-covered logic
* Stable mapping behavior

---

## 5.4 Strict HTTP Family Guard

Ensures:

* Status codes match their semantic family
* Prevents category/status mismatch
* Validates correct usage patterns

Example:

* Validation → 4xx
* Internal → 5xx

No cross-family leakage allowed.

---

## 5.5 Policy-Driven Validation

Policies determine:

* Retryable behavior
* Safe exposure rules
* Escalation boundaries
* Validation enforcement

All policies are:

* Deterministic
* Explicit
* Injectable
* Testable

---

## 5.6 Global Policy Injection Support

Allows:

* Centralized configuration
* Application-wide consistency
* No hidden global state mutation
* Controlled policy override

---

# 6️⃣ Determinism Guarantees

1. Same input → same output.
2. No implicit environment usage.
3. No hidden randomness.
4. No time-based behavior.
5. No side effects.

---

# 7️⃣ Stability Contract (v1.0.0)

The following are frozen for 1.x:

* Exception behavior
* Category identity rules
* Escalation engine logic
* HTTP family validation rules
* Public constructors and methods

Breaking changes only allowed in 2.0.

---

# 8️⃣ Security Guarantees

* Safe message handling.
* No stack trace exposure by default.
* No automatic environment leaks.
* No transport-layer coupling.

---

# 9️⃣ Quality Standards

* 100% test coverage.
* PHPUnit 11.
* PHPStan clean.
* Deterministic tests.
* No flaky behavior.

---

# 🔚 Phase 1 Result

Phase 1 successfully delivers:

> Deterministic Exception Kernel for Modern PHP Applications

Ready for ecosystem expansion in Phase 2.

---
