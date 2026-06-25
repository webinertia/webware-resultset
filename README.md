# webware-resultset

[![PHP Version](https://img.shields.io/badge/php-~8.4%20%7C%7C%20~8.5-blue)](https://www.php.net/)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%2010-brightgreen)](phpstan.neon.dist)
[![License](https://img.shields.io/badge/license-BSD--3--Clause-green)](LICENSE)

A ResultSet implementation for [PhpDb](https://github.com/php-db/phpdb) that delegates row object creation to the prototype's `withRowData()` method.

## Overview

`webware-resultset` extends PhpDb's ResultSet system with a prototype-driven row population strategy. The standard `HydratingResultSet` **clones** the row prototype and calls `exchangeArray()` on each clone — the ResultSet controls object creation. `WithRowDataResultSet` instead calls `withRowData(array $data)` on the prototype, and the **prototype controls** how each row object is created and populated.

The prototype acts as a factory: it receives raw row data and returns a populated row object. The ResultSet never clones the prototype — the prototype implementation decides the creation strategy (`new static()`, a factory method, etc.).

This pattern is useful when:

- Row classes need to control their own instantiation (constructor injection, validation, transformation).
- You want a single `withRowData()` method to handle both hydration and creation, rather than a separate clone step.
- Your row objects have constructor dependencies that can't be satisfied by cloning.

## Installation

```bash
composer require webware/webware-resultset
```

## Requirements

- [php-db/phpdb](https://github.com/php-db/phpdb) ^0.6.0 (dev dependency — provides the base `AbstractResultSet`)

## Components

### `WithRowDataPrototypeInterface`

```php
namespace Webware\ResultSet;

use PhpDb\ResultSet\RowPrototypeInterface;

interface WithRowDataPrototypeInterface extends RowPrototypeInterface
{
    /**
     * @param array<array-key, mixed> $withRowData
     */
    public function withRowData(array $withRowData): static;
}
```

Extends PhpDb's `RowPrototypeInterface` and adds a `withRowData()` method. Row prototype classes must implement this interface to be used with `WithRowDataResultSet`.

The `withRowData()` method receives raw row data as an associative array and returns a populated row object (`static`). The prototype acts as a factory — it is not mutated by the ResultSet. The implementation decides how row objects are created: via `new static()`, a clone, or any other strategy.

### `WithRowDataResultSet`

```php
final class WithRowDataResultSet extends AbstractResultSet
```

A concrete, `final` ResultSet that overrides three methods from `AbstractResultSet`:

| Method | Behavior |
| --- | --- |
| `current(): ?WithRowDataPrototypeInterface` | Calls `parent::current()`. If the result is an array, passes it to `getRowPrototype()->withRowData($data)` and returns the row object produced by the prototype. Returns `null` otherwise. |
| `setRowPrototype(...)` | Accepts only `WithRowDataPrototypeInterface` instances. Throws `InvalidArgumentException` if given a plain `ArrayObject` or `RowPrototypeInterface`. |
| `getRowPrototype()` | Returns the prototype with narrowed type `WithRowDataPrototypeInterface` (instead of `?object`). |

All other ResultSet behavior — initialization, buffering, iteration, counting, `toArray()` — is inherited unchanged from `AbstractResultSet`.

## Usage

### 1. Create a row prototype

```php
use Webware\ResultSet\WithRowDataPrototypeInterface;

final class UserRow implements WithRowDataPrototypeInterface
{
    public function __construct(
        public readonly int $id = 0,
        public readonly string $name = '',
        public readonly string $email = '',
    ) {}

    public function withRowData(array $withRowData): static
    {
        return new self(
            id:    (int) ($withRowData['id'] ?? 0),
            name:  (string) ($withRowData['name'] ?? ''),
            email: (string) ($withRowData['email'] ?? ''),
        );
    }

    public function exchangeArray(array $array): array
    {
        return $this->withRowData($array)->toArray();
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
        ];
    }
}
```

### 2. Use the ResultSet

```php
use Webware\ResultSet\WithRowDataResultSet;

$resultSet = new WithRowDataResultSet(new UserRow());

// Initialize with query results (array of rows)
$resultSet->initialize([
    ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
    ['id' => 2, 'name' => 'Bob',   'email' => 'bob@example.com'],
]);

foreach ($resultSet as $user) {
    echo $user->name; // Alice, then Bob
    // $user is a new UserRow instance each iteration, created by withRowData()
}
```

### 3. Changing the row prototype at runtime

```php
$resultSet->setRowPrototype(new AdminRow());

// Throws InvalidArgumentException — plain RowPrototypeInterface is rejected:
$resultSet->setRowPrototype(new ArrayObject());
```

## Development

```bash
# Install dependencies
composer install

# Run all checks (coding standard, static analysis, tests)
composer check-all

# Individual checks
composer cs-check       # PHP-CS-Fixer
composer sa             # PHPStan static analysis
composer test           # PHPUnit
composer test-coverage  # PHPUnit with coverage report
```

## License

BSD 3-Clause License. See [LICENSE](LICENSE) for details.
