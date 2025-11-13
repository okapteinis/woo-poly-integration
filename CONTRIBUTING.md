# Contributing to WooCommerce Polylang Integration

Thank you for your interest in contributing to WooCommerce Polylang Integration! This document provides guidelines and instructions for contributing to the project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Workflow](#development-workflow)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Pull Request Process](#pull-request-process)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code. Please be respectful and considerate in your interactions with other contributors.

## Getting Started

### Prerequisites

- PHP 7.4 or higher
- Composer
- Git
- WordPress development environment
- WooCommerce and Polylang plugins

### Development Setup

1. **Fork and clone the repository:**

```bash
git clone https://github.com/YOUR-USERNAME/woo-poly-integration.git
cd woo-poly-integration
```

2. **Checkout the nightly branch:**

```bash
git checkout nightly
```

3. **Install dependencies:**

```bash
composer install
```

4. **Set up pre-commit hooks (optional but recommended):**

```bash
cp .git-hooks/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

### Project Structure

```
woo-poly-integration/
├── .github/           # GitHub Actions workflows
├── src/               # Source code
│   └── Hyyan/WPI/     # Main plugin classes
├── tests/             # Test files
│   ├── Unit/          # Unit tests
│   └── Integration/   # Integration tests
├── assets/            # Frontend assets
├── languages/         # Translation files
└── vendor/            # Composer dependencies
```

## Development Workflow

### Branching Strategy

- `master` - Stable releases only
- `nightly` - Active development (default branch)
- `feature/*` - New features
- `fix/*` - Bug fixes
- `refactor/*` - Code refactoring

### Creating a Feature Branch

```bash
git checkout nightly
git pull origin nightly
git checkout -b feature/your-feature-name
```

### Making Changes

1. Make your changes in your feature branch
2. Write or update tests for your changes
3. Run the test suite to ensure everything passes
4. Run code quality checks
5. Commit your changes with clear, descriptive messages

### Testing Your Changes

Before submitting your changes:

```bash
# Run all tests
composer test

# Run code style checks
composer cs

# Run static analysis
composer analyze

# Run all CI checks
composer ci
```

## Coding Standards

### PHP Coding Standards

We follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/) with some modifications for modern PHP practices.

**Key guidelines:**

- Use PSR-12 naming conventions for classes and namespaces
- Use type hints for method parameters and return types (PHP 7.4+)
- Document all public methods with PHPDoc blocks
- Use short array syntax `[]` instead of `array()`
- Keep methods focused and concise
- Avoid deeply nested code (max 3 levels)

### Code Formatting

Run PHP CodeSniffer to check your code:

```bash
composer cs
```

Automatically fix code style issues:

```bash
composer cs:fix
```

### Static Analysis

Run PHPStan to catch potential errors:

```bash
composer analyze
```

### Documentation

- Add PHPDoc blocks to all classes and methods
- Include `@param` and `@return` tags
- Document complex logic with inline comments
- Update README.md if adding new features

Example:

```php
/**
 * Get the current product language
 *
 * @param int $product_id The product ID
 * @return string|false The language code or false if not found
 */
public function getProductLanguage(int $product_id) {
    // Implementation
}
```

## Testing

### Writing Tests

All new features and bug fixes should include tests.

**Unit Tests** - Test individual classes in isolation:

```php
<?php
namespace Hyyan\WPI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Hyyan\WPI\YourClass;

class YourClassTest extends TestCase
{
    public function testYourMethod(): void
    {
        $instance = new YourClass();
        $result = $instance->yourMethod();

        $this->assertEquals('expected', $result);
    }
}
```

**Integration Tests** - Test component interactions:

```php
<?php
namespace Hyyan\WPI\Tests\Integration;

use PHPUnit\Framework\TestCase;

class FeatureIntegrationTest extends TestCase
{
    public function testFeatureWorksWithWooCommerce(): void
    {
        // Test actual integration
    }
}
```

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/phpunit tests/Unit/YourClassTest.php

# Run with coverage
composer test:coverage
```

### Coverage Goals

- Aim for at least 60% code coverage
- Focus on critical components first
- Test edge cases and error conditions

## Pull Request Process

### Before Submitting

1. ✅ All tests pass (`composer test`)
2. ✅ Code follows standards (`composer cs`)
3. ✅ Static analysis passes (`composer analyze`)
4. ✅ Changes are documented
5. ✅ Commits have clear messages

### Commit Message Format

Use clear, descriptive commit messages:

```
Add feature to sync product attributes with translations

- Implement attribute synchronization
- Add tests for attribute sync
- Update documentation

Fixes #123
```

### Submitting a Pull Request

1. Push your branch to your fork:

```bash
git push origin feature/your-feature-name
```

2. Open a pull request against the `nightly` branch

3. Fill out the PR template with:
   - Description of changes
   - Related issue numbers
   - Testing performed
   - Screenshots (if applicable)

4. Wait for code review and address feedback

5. Once approved, a maintainer will merge your PR

### Pull Request Checklist

- [ ] PR targets the `nightly` branch
- [ ] Code follows project coding standards
- [ ] Tests are included and passing
- [ ] Documentation is updated
- [ ] No merge conflicts
- [ ] Commit messages are clear
- [ ] Changes are backwards compatible (or breaking changes are documented)

## Reporting Bugs

### Before Reporting

1. Check if the bug has already been reported
2. Test with the latest version
3. Verify it's not a configuration issue

### Bug Report Template

```markdown
**Describe the bug**
A clear description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '....'
3. See error

**Expected behavior**
What you expected to happen.

**Environment:**
- WordPress version:
- WooCommerce version:
- Polylang version:
- Plugin version:
- PHP version:
- Theme:

**Additional context**
Any other relevant information.
```

## Suggesting Features

### Feature Request Template

```markdown
**Is your feature request related to a problem?**
Description of the problem.

**Describe the solution you'd like**
A clear description of what you want to happen.

**Describe alternatives you've considered**
Other solutions you've thought about.

**Additional context**
Any other relevant information.
```

## Code Review Process

- All submissions require review by at least one maintainer
- Reviewers will check for:
  - Code quality and standards compliance
  - Test coverage
  - Documentation
  - Performance implications
  - Security concerns
  - Backwards compatibility

## Need Help?

- 📖 Read the [documentation](README.md)
- 💬 Ask questions in [GitHub Discussions](https://github.com/hyyan/woo-poly-integration/discussions)
- 🐛 Report bugs via [GitHub Issues](https://github.com/hyyan/woo-poly-integration/issues)

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

## Recognition

Contributors will be recognized in:
- CHANGELOG.md
- GitHub contributors page
- Release notes

Thank you for contributing to WooCommerce Polylang Integration! 🎉
