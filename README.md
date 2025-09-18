# Rector Rules

[![Tests](https://github.com/simtel/rector-rules/workflows/Tests/badge.svg)](https://github.com/simtel/rector-rules/actions)
[![Code Quality](https://github.com/simtel/rector-rules/workflows/Code%20Quality/badge.svg)](https://github.com/simtel/rector-rules/actions)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://phpstan.org/)

A collection of custom Rector rules for automated PHP code refactoring.

## Overview

This package provides custom Rector rules to help modernize and improve PHP codebases through automated refactoring. Currently includes the `RenameFindAndGetMethodCallRector` rule.

## Requirements

- PHP 8.3+
- Rector 1.0+

## Installation

Clone this repository and install dependencies:

```bash
git clone <repository-url>
cd rector-rules
composer install
```

## Rules

### RenameFindAndGetMethodCallRector

Automatically renames `find*` methods to `get*` when they return a non-nullable entity type.

#### What it does

This rule enforces a naming convention where:
- Methods starting with `find` that return nullable types (e.g., `?User`) keep their name
- Methods starting with `find` that return non-nullable entity types are renamed to `get`

This follows the common convention where:
- `find*` methods may return `null` when the entity is not found
- `get*` methods always return an entity and throw exceptions when not found

#### Before

```php
class UserRepository
{
    public function findUserById(int $id): User
    {
        // Always returns User, never null
        return $this->entityManager->find(User::class, $id) 
            ?? throw new UserNotFoundException();
    }
    
    public function findUserByEmail(string $email): ?User
    {
        // May return null
        return $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }
}
```

#### After

```php
class UserRepository
{
    public function getUserById(int $id): User  // Renamed find -> get
    {
        // Always returns User, never null
        return $this->entityManager->find(User::class, $id) 
            ?? throw new UserNotFoundException();
    }
    
    public function findUserByEmail(string $email): ?User  // Unchanged (nullable)
    {
        // May return null
        return $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }
}
```

#### Rules for transformation

The rule will rename a method from `find*` to `get*` if:
1. Method name starts with "find"
2. Has a return type declaration
3. Return type is not nullable (`?Type`)
4. Return type is not a union type
5. Return type is not a primitive type (int, string, bool, float, array, object, mixed, void)

## Usage

### Manual Configuration

Create a `rector.php` configuration file:

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Simtel\RectorRules\Rector\RenameFindAndGetMethodCallRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);

    $rectorConfig->rule(RenameFindAndGetMethodCallRector::class);
};
```

### Running Rector

Execute the refactoring:

```bash
# Dry run (shows what would be changed)
vendor/bin/rector process --dry-run

# Apply changes
vendor/bin/rector process
```

## Development

### Continuous Integration

This project uses GitHub Actions for automated testing and code quality checks:

- **Tests Workflow**: Runs PHPUnit tests on PHP 8.3 and 8.4 with both lowest and stable dependencies
- **Code Quality Workflow**: Performs static analysis with PHPStan, syntax checking, and security audits
- **Coverage**: Test coverage reports are uploaded to Codecov

### Local Development Scripts

```bash
# Run all tests
composer test

# Run tests with coverage report
composer test-coverage

# Run static analysis
composer analyse

# Run both analysis and tests
composer check
```

### Running Tests

```bash
vendor/bin/phpunit
```

### Code Analysis

```bash
vendor/bin/phpstan analyse
```

### Project Structure

```
rector-rules/
├── src/
│   └── Rector/
│       └── RenameFindAndGetMethodCallRector.php
├── tests/
│   └── RenameFindAndGetMethodCallRector/
│       ├── config/
│       │   └── configured_rule.php
│       ├── Fixture/
│       │   └── some_class.php.inc
│       └── RenameFindAndGetMethodCallRectorTest.php
├── composer.json
├── phpunit.xml
└── README.md
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Add your changes with tests
4. Ensure all tests pass
5. Submit a pull request

## License

This project is open source. Please check the license file for more details.

## Author

Created by Simtel

---

For more information about Rector, visit [https://getrector.org/](https://getrector.org/)