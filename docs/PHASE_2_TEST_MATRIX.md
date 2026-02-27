# Maatify Exceptions — Phase 2

## Test Matrix (Deterministic & Contract Validation)

> Status: LOCKED
> Scope: All new Phase 2 components
> Coverage target: 100% (Classes / Methods / Lines)

---

# 1️⃣ Test Categories Overview

| Section | Purpose                             |
|---------|-------------------------------------|
| A       | NormalizedError tests               |
| B       | Throwable mapping tests             |
| C       | JSON formatter tests                |
| D       | RFC7807 formatter tests             |
| E       | ErrorSerializer orchestration tests |
| F       | Determinism golden tests            |
| G       | Edge & safety tests                 |

---

# 2️⃣ A — NormalizedError Tests

### A1 — Construction integrity

* Create NormalizedError with valid inputs
* Assert:

    * All getters return exact values
    * meta is preserved
    * No mutation possible

---

### A2 — Meta always present

* Pass empty array as meta
* Assert `meta === []`

---

### A3 — Immutability

* Ensure no setters exist
* Ensure internal state cannot be modified after construction

---

# 3️⃣ B — Throwable Mapping Tests

---

## B1 — MaatifyException mapping

Input:

* MaatifyException with:

    * code: VALIDATION_FAILED
    * status: 400
    * category: validation
    * retryable: false
    * safe: true
    * meta: ['field' => 'email']

Assert:

* NormalizedError fields match exactly

---

## B2 — External Throwable fallback

Input:

* new \RuntimeException("DB exploded")

Assert:

* code = INTERNAL_ERROR
* message = "An unexpected error occurred."
* status = 500
* category = internal
* retryable = false
* safe = true
* meta = []

---

## B3 — Deterministic fallback

Call mapper twice with same RuntimeException

Assert:

* Both NormalizedError objects produce identical field values

---

# 4️⃣ C — JsonErrorFormatter Tests

---

## C1 — Basic JSON formatting

Input:

* NormalizedError(valid 400)
* Context without traceId

Assert body:

```json
{
  "error": {
    "code": "...",
    "message": "...",
    "status": 400,
    "category": "...",
    "retryable": false,
    "safe": true,
    "meta": {}
  }
}
```

Assert:

* trace_id absent
* contentType = application/json; charset=utf-8
* headers = []

---

## C2 — trace_id inclusion

Input:

* Context(traceId="abc123")

Assert:

* body contains "trace_id": "abc123"

---

## C3 — meta always present

Even when meta = []

Assert:

* body["error"]["meta"] exists
* is array
* not null

---

## C4 — Status consistency

Assert:

* ErrorResponseModel.status === body["error"]["status"]

---

# 5️⃣ D — RFC7807 Formatter Tests

---

## D1 — Basic RFC structure

Assert presence of:

* type
* title
* status
* detail

---

## D2 — Extensions always present

Assert:

* body["extensions"] exists
* contains:

    * code
    * category
    * retryable
    * safe
    * meta

---

## D3 — Instance inclusion

If context.instance="/foo"

Assert:

* body["instance"] === "/foo"

If null:

* instance key omitted

---

## D4 — Title mapping correctness

Category → Title mapping:

| Category       | Expected Title          |
|----------------|-------------------------|
| validation     | Validation failed       |
| authentication | Authentication required |
| authorization  | Permission denied       |
| conflict       | Conflict                |
| internal       | Internal error          |

Assert mapping works.

---

## D5 — Content type

Assert:

* contentType = application/problem+json; charset=utf-8

---

# 6️⃣ E — ErrorSerializer Orchestration Tests

---

## E1 — Serializer uses mapper

Inject fake mapper returning known NormalizedError
Assert serializer returns formatted response.

---

## E2 — Serializer uses injected formatter

Inject custom formatter
Assert serializer delegates correctly.

---

## E3 — Full pipeline test

Throwable → DefaultThrowableToError → JsonFormatter → ResponseModel

Assert:

* Correct body
* Correct status
* Deterministic output

---

# 7️⃣ F — Determinism Golden Tests

---

## F1 — JSON determinism

Same NormalizedError + same context → identical array (strict equality)

---

## F2 — RFC determinism

Same input → identical output array

---

## F3 — No random fields

Assert:

* No timestamp key
* No uuid key
* No stack trace
* No dynamic content

---

# 8️⃣ G — Safety & Security Tests

---

## G1 — External exception message not leaked

Input:

* new RuntimeException("Sensitive DB error")

Assert:

* Output message = "An unexpected error occurred."
* No original message inside body

---

## G2 — Meta isolation

Ensure meta returned exactly as provided
No implicit data injected.

---

## G3 — Debug flag does not alter output

Context(debug=true)

Assert:

* No stack trace
* No debug data appears

---

# 9️⃣ Coverage Enforcement

All new classes must:

* Have full test coverage
* Include edge-case tests
* Include deterministic equality tests

---

# 🔟 Completion Criteria

Phase 2 considered complete only if:

* All matrix cases implemented.
* Golden tests pass.
* No drift from Execution Spec.
* 100% coverage preserved.

---

# 🔚 Final Note

This matrix is authoritative for Phase 2.
No implementation is valid unless all tests pass.

---
