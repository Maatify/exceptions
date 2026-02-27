# maatify/exceptions

[![Latest Version](https://img.shields.io/packagist/v/maatify/exceptions.svg?style=for-the-badge)](https://packagist.org/packages/maatify/exceptions)
[![PHP Version](https://img.shields.io/packagist/php-v/maatify/exceptions.svg?style=for-the-badge)](https://packagist.org/packages/maatify/exceptions)
[![License](https://img.shields.io/packagist/l/maatify/exceptions.svg?style=for-the-badge)](LICENSE)

![PHPStan](https://img.shields.io/badge/PHPStan-Level%20Max-4E8CAE)

[![Changelog](https://img.shields.io/badge/Changelog-View-blue)](CHANGELOG.md)
[![Security](https://img.shields.io/badge/Security-Policy-important)](SECURITY.md)

![Monthly Downloads](https://img.shields.io/packagist/dm/maatify/exceptions?label=Monthly%20Downloads&color=00A8E8)
![Total Downloads](https://img.shields.io/packagist/dt/maatify/exceptions?label=Total%20Downloads&color=2AA9E0)

**Enterprise-grade, hardened exception handling library for PHP applications.**

`maatify/exceptions` provides a strictly typed, immutable taxonomy for application errors, enforcing semantic correctness, security, and consistent HTTP mapping. It is designed to prevent "taxonomy drift" and ensure that critical system errors are never masked by lower-severity wrappers.

---

## 🚀 Key Features

*   **Strict Taxonomy:** Exceptions are categorized into 9 distinct families (System, Validation, Auth, etc.) backed by `ErrorCategoryEnum`.
*   **Guarded Overrides:** Prevents developers from accidentally mismatching error codes or HTTP statuses.
*   **Escalation Protection:** Automatically escalates severity when a critical exception is wrapped in a lighter one (e.g., a Database failure wrapped in a generic runtime exception will typically retain 500 status).
*   **Error Serialization:** Transforms any `Throwable` into a standardized, deterministic JSON response suitable for APIs.
*   **RFC7807 Support:** Built-in formatter for "Problem Details for HTTP APIs" compliance.
*   **Zero Dependencies:** Pure PHP implementation. No framework coupling.
*   **PSR-4 Compliant:** Ready for immediate Composer autoloading.

---

## Requirements

- PHP 8.2+

---

## 📦 Installation

```bash
composer require maatify/exceptions
```

---

## 📖 Usage

### Basic Usage

Throwing a predefined exception:

```php
use Maatify\Exceptions\Exception\Validation\InvalidArgumentMaatifyException;

throw new InvalidArgumentMaatifyException('The email format is invalid.');
// Result:
// Category: VALIDATION
// HTTP Status: 400
// Error Code: INVALID_ARGUMENT
```

### Advanced Usage (Wrapping)

When catching a low-level error and re-throwing, the library automatically handles severity escalation:

```php
use Maatify\Exceptions\Exception\BusinessRule\BusinessRuleMaatifyException;
use Maatify\Exceptions\Exception\System\DatabaseConnectionMaatifyException;

try {
    // Simulate a critical database failure (System / 503)
    throw new DatabaseConnectionMaatifyException('Connection timeout');
} catch (DatabaseConnectionMaatifyException $e) {
    // Attempting to wrap it in a "softer" business exception
    // Note: BusinessRuleMaatifyException is abstract, so we use an anonymous class for this example
    throw new class('Unable to process order', 0, $e) extends BusinessRuleMaatifyException {};
}

// RESULT:
// The final exception will report:
// Category: SYSTEM (Escalated from BusinessRule)
// HTTP Status: 503 (Escalated from 422)
// This ensures monitoring tools see the root cause (System Failure), not a generic Business Rule error.
```

### Error Serialization

Convert any exception into a ready-to-send API response:

```php
use Maatify\Exceptions\Application\Error\ErrorSerializer;
use Maatify\Exceptions\Application\Error\DefaultThrowableToError;
use Maatify\Exceptions\Application\Format\JsonErrorFormatter;

$serializer = new ErrorSerializer(
    new DefaultThrowableToError(),
    new JsonErrorFormatter()
);

try {
    // ... application logic
} catch (\Throwable $t) {
    $response = $serializer->serialize($t);
    
    // Send to client
    http_response_code($response->getStatus());
    header('Content-Type: ' . $response->getContentType());
    echo json_encode($response->getBody());
}
```

---

## 📚 Documentation

Detailed documentation is available in the [BOOK/](BOOK/) directory:

1.  [Introduction](BOOK/01_Introduction.md)
2.  [Architecture](BOOK/02_Architecture.md)
3.  [Taxonomy](BOOK/03_Taxonomy.md)
4.  [Exception Families](BOOK/04_Exception_Families.md)
5.  [Override Rules](BOOK/05_Override_Rules.md)
6.  [Escalation Protection](BOOK/06_Escalation_Protection.md)
7.  [Security Model](BOOK/07_Security_Model.md)
8.  [Best Practices](BOOK/08_Best_Practices.md)
9.  [Extending The Library](BOOK/09_Extending_The_Library.md)
10. [API Integration Guide](BOOK/10_API_Integration_Guide.md)
11. [Testing Strategy](BOOK/11_Testing_Strategy.md)
12. [Versioning Policy](BOOK/12_Versioning_Policy.md)
13. [Packagist Metadata](BOOK/13_Packagist_Metadata.md)

---

## 🛡️ Guarantees

*   **Category Immutability:** An exception's category is defined by its class and cannot be overridden at runtime.
*   **Status Class Safety:** You cannot force a 4xx exception to return a 5xx status code manually, or vice versa.
*   **Escalation Determinism:** Severity calculation is deterministic and side-effect free.
*   **Serialization Determinism:** The same input Throwable and Context will always yield the exact same output.

---

## ✅ Quality Status

- PHP 8.2+
- PHPUnit 11
- 100% Code Coverage
- Zero Warnings
- Immutable Exception Design
- Deterministic Escalation & Policy Engine

---

## 🪪 License

This library is licensed under the **MIT License**.  
See the [LICENSE](LICENSE) file for details.

---

## 👤 Author

Engineered by **Mohamed Abdulalim** ([@megyptm](https://github.com/megyptm))  
Backend Lead & Technical Architect  
https://www.maatify.dev

---

## 🤝 Contributors

Special thanks to the Maatify.dev engineering team and all open-source contributors.  
Contributions are welcome.

---

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a> — Unified Ecosystem for Modern PHP Libraries</sub>
</p>