# Git Hooks for WooCommerce Polylang Integration

This directory contains Git hooks that help maintain code quality.

## Available Hooks

### pre-commit

Runs before each commit to ensure code quality:

- ✅ PHP Lint - Checks for PHP syntax errors
- ✅ PHPCS - Enforces WordPress coding standards
- ✅ PHPStan - Performs static analysis
- ✅ PHPUnit - Runs unit tests

## Installation

To install the pre-commit hook:

```bash
cp .git-hooks/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

Or use this one-liner:

```bash
cp .git-hooks/pre-commit .git/hooks/pre-commit && chmod +x .git/hooks/pre-commit
```

## Usage

Once installed, the hook will run automatically before each commit.

If checks fail, the commit will be aborted. Fix the issues and try committing again.

## Skipping Hooks (Not Recommended)

In rare cases where you need to bypass the hooks:

```bash
git commit --no-verify -m "Your commit message"
```

**Warning:** Only use `--no-verify` in exceptional circumstances. Bypassing hooks can lead to code quality issues.

## Customization

You can modify the hooks in this directory, but remember to reinstall them after making changes:

```bash
cp .git-hooks/pre-commit .git/hooks/pre-commit
```

## Troubleshooting

### Hook is not running

Make sure the hook is:
1. Installed in `.git/hooks/`
2. Executable (`chmod +x .git/hooks/pre-commit`)

### Hook fails with "command not found"

Ensure you have:
1. Composer installed
2. Run `composer install` to install dependencies
3. PHP available in your PATH

### Tests are too slow

You can comment out the test step in the pre-commit hook if it's too slow for your workflow. However, make sure to run tests before pushing!

## Best Practices

- Install hooks when you first clone the repository
- Don't skip hooks unless absolutely necessary
- If hooks fail, fix the issues rather than bypassing them
- Run `composer ci` before pushing to ensure all checks pass
