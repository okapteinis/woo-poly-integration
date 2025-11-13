# WooCommerce Polylang Integration - Test Suite

This directory contains the test suite for the WooCommerce Polylang Integration plugin.

## Directory Structure

```
tests/
├── Unit/           # Unit tests for individual classes and methods
├── Integration/    # Integration tests for component interactions
├── bootstrap.php   # Test environment bootstrap
└── README.md       # This file
```

## Running Tests

### Prerequisites

Install development dependencies:

```bash
composer install
```

### Run All Tests

```bash
composer test
```

### Run with Coverage Report

```bash
composer test:coverage
```

This will generate an HTML coverage report in the `coverage/` directory.

### Run Specific Test Suite

```bash
# Unit tests only
vendor/bin/phpunit --testsuite="Unit Tests"

# Integration tests only
vendor/bin/phpunit --testsuite="Integration Tests"
```

### Run Specific Test File

```bash
vendor/bin/phpunit tests/Unit/ExampleTest.php
```

## Writing Tests

### Unit Tests

Unit tests should test individual classes or methods in isolation. Place them in `tests/Unit/`.

Example:

```php
<?php
namespace Hyyan\WPI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Hyyan\WPI\YourClass;

class YourClassTest extends TestCase
{
    public function testSomething(): void
    {
        $instance = new YourClass();
        $result = $instance->someMethod();

        $this->assertEquals('expected', $result);
    }
}
```

### Integration Tests

Integration tests should test how multiple components work together. Place them in `tests/Integration/`.

### Test Naming Conventions

- Test classes should be named `{ClassName}Test`
- Test methods should start with `test` followed by a descriptive name in camelCase
- Use descriptive names that explain what is being tested

### Assertions

PHPUnit provides many assertion methods:

- `assertEquals($expected, $actual)` - Assert two values are equal
- `assertTrue($condition)` - Assert condition is true
- `assertFalse($condition)` - Assert condition is false
- `assertNull($value)` - Assert value is null
- `assertCount($count, $array)` - Assert array has specific count
- `assertInstanceOf($class, $object)` - Assert object is instance of class

See [PHPUnit documentation](https://phpunit.de/documentation.html) for complete list.

## Test Coverage Goals

- Target: 60% code coverage initially
- Focus on critical components first:
  - Order.php
  - Utilities.php
  - RestAPI.php
  - Blocks.php

## Continuous Integration

Tests run automatically on every pull request via GitHub Actions. See `.github/workflows/tests.yml` for configuration.

## Troubleshooting

### Tests fail with "Class not found" error

Make sure you've run `composer install` to install dependencies and generate the autoloader.

### Tests fail with WordPress function errors

The `tests/bootstrap.php` file includes mocks for common WordPress functions. You may need to add additional mocks for functions used in your code.

## Contributing

When adding new features or fixing bugs, please include corresponding tests. See [CONTRIBUTING.md](../CONTRIBUTING.md) for more details.
