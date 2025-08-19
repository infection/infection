# Copilot Instructions for Infection

## About this Repository

Infection is a PHP mutation testing framework that helps developers measure the effectiveness of their test suites. It works by making small modifications (mutations) to your code and checking if your tests can detect these changes.

## Key Concepts

- **Mutation Testing**: A technique to evaluate test quality by introducing small changes to code
- **Mutants**: Modified versions of the original code with small changes
- **Mutation Score Indicator (MSI)**: Percentage of mutants killed by tests
- **Mutators**: Classes that apply specific types of mutations to code

## Architecture and Structure

- `src/` - Main source code organized by functionality:
  - `Mutator/` - Contains all mutation operators
  - `TestFramework/` - Adapters for different testing frameworks (PHPUnit, etc.)
  - `Configuration/` - Configuration parsing and validation
  - `Console/` - CLI application commands
  - `Process/` - Process management for running tests
  - `Logger/` - Output formatting and logging
- `tests/` - Comprehensive test suite
- `resources/` - JSON schema and other resources
- `bin/infection` - Main executable

## Technology Stack

- **PHP 8.2+** - Modern PHP with strict typing
- **Symfony Components** - Console, Process, Filesystem, Finder
- **nikic/php-parser** - For PHP code parsing and manipulation
- **PHPUnit** - Primary testing framework
- **Composer** - Dependency management

## Development Workflow

### Setup
```bash
composer install
./setup_environment.sh  # Sets up pre-push hooks (Unix-like systems)
```

### Quality Assurance
```bash
make test      # Run full test suite (requires Docker)
make cs        # Fix code style
make phpstan   # Static analysis
make rector    # Automated refactoring
```

### Key Configuration Files
- `infection.json5` - Infection's own configuration
- `phpunit.xml.dist` - PHPUnit configuration
- `.php-cs-fixer.php` - Code style rules
- `psalm.xml` - Static analysis configuration

## Coding Standards

- **PSR-12** compliant code style
- **Strict typing** - Always use declare(strict_types=1)
- **Comprehensive tests** - New features require tests, bug fixes must include regression tests
- **Immutable objects** - Prefer readonly properties and value objects
- **Type safety** - Use type hints, avoid mixed types

## Testing Philosophy

- Unit tests for individual components
- Integration tests for component interactions
- End-to-end tests for CLI functionality
- Mutation testing on itself (dogfooding)
- Tests must be deterministic and fast

## Common Patterns

### Mutator Implementation
```php
final class SomeMutator implements Mutator
{
    public function canMutate(Node $node): bool
    {
        // Check if node can be mutated
    }

    public function mutate(Node $node): iterable
    {
        // Return mutations
    }
}
```

### Value Objects
```php
final readonly class SomeValue
{
    public function __construct(
        public string $property,
    ) {}
}
```

### Configuration Classes
Use readonly classes with clear validation and meaningful error messages.

## When Contributing

- Target the `master` branch
- Include tests for new behaviors
- Fixed bugs MUST have regression tests
- Run `make cs` before committing
- Consider opening an issue for major features first
- Follow existing patterns for consistency

## Common Issues to Avoid

- Don't break backward compatibility without discussion
- Avoid adding dependencies unnecessarily
- Don't skip tests - they're critical for a testing tool
- Be careful with performance - mutations can be CPU intensive
- Maintain Windows compatibility where possible

## Key Files to Understand

- `src/Engine.php` - Main execution engine
- `src/Configuration/` - Configuration system
- `src/Mutator/` - All mutation operators
- `src/TestFramework/` - Test framework integrations
- `src/Process/` - Process execution logic

## Performance Considerations

- Infection processes many files and runs many test executions
- Memory efficiency is important for large codebases
- Parallel execution is used extensively
- File I/O should be minimized
- Consider caching where appropriate

This is a mature, production-ready tool used by many PHP projects. Code quality and reliability are paramount.