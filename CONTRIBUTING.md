# Contributing to Laravel Simple Permissions

Thank you for considering contributing to Laravel Simple Permissions! This document provides guidelines and instructions for contributing.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for everyone.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the issue list as you might find out that you don't need to create one. When you are creating a bug report, please include as many details as possible:

- **Clear title and description**
- **Steps to reproduce the issue**
- **Expected behavior**
- **Actual behavior**
- **Environment details** (PHP version, Laravel version, etc.)
- **Code examples** if applicable

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, please include:

- **Clear title and description**
- **Use case** - Why is this feature useful?
- **Proposed solution** - How should it work?
- **Alternatives considered** - What other approaches did you consider?

### Pull Requests

1. **Fork the repository**
2. **Create your feature branch** (`git checkout -b feature/amazing-feature`)
3. **Make your changes**
4. **Run tests** (`vendor/bin/pest`)
5. **Run code style checks** (`vendor/bin/pint --test`)
6. **Run static analysis** (`vendor/bin/phpstan analyse`)
7. **Commit your changes** (`git commit -m 'Add some amazing feature'`)
8. **Push to the branch** (`git push origin feature/amazing-feature`)
9. **Open a Pull Request**

## Development Setup

1. Clone the repository:
```bash
git clone https://github.com/squareetlabs/laravel-simple-permissions.git
cd laravel-simple-permissions
```

2. Install dependencies:
```bash
composer install
```

3. Run tests:
```bash
vendor/bin/pest
```

4. Check code style:
```bash
vendor/bin/pint --test
```

5. Run static analysis:
```bash
vendor/bin/phpstan analyse
```

## Coding Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Use Laravel Pint for code formatting
- Write meaningful commit messages
- Add PHPDoc comments for classes and methods
- Write tests for new features

## Testing

- All new features must include tests
- Tests should be written using Pest
- Aim for high test coverage
- Run tests before submitting a PR

## Commit Messages

- Use clear and descriptive commit messages
- Start with a verb in imperative mood (e.g., "Add", "Fix", "Update")
- Reference issue numbers when applicable

Example:
```
Add support for global groups

This feature allows creating groups to grant users access
across the application.

Fixes #123
```

## Pull Request Process

1. Ensure your code follows the coding standards
2. Update the CHANGELOG.md with your changes
3. Update documentation if needed
4. Ensure all tests pass
5. Ensure code style checks pass
6. Ensure static analysis passes
7. Request review from maintainers

## Questions?

If you have any questions, please open an issue for discussion.

Thank you for contributing! 🎉

