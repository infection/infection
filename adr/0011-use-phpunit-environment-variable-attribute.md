# Use PHPUnit attributes for test environment variables

## Context

Changes to process environment variables can affect later tests if the original values are
not restored. Infection previously prevented this with `BacksUpEnvironmentVariables`, which
captured the complete process environment in `setUp()` and restored it in `tearDown()`.

An AutoReview rule required the canonical test of code containing `putenv()` to use that
trait. The rule searched source text for a limited set of function-call forms, so it could
miss unqualified calls, calls in fixtures and helpers, and indirect environment usage. The
trait also required bespoke snapshot code and manual lifecycle calls in each affected test.

[PHPUnit][phpunit] 12.1.0 provides the repeatable
[`#[WithEnvironmentVariable]`][with-env-variable-doc] attribute. It sets a named environment
variable before a test and restores its previous `getenv()` and `$_ENV` values afterwards.
This makes each dependency explicit and removes the need to capture every environment
variable.

> [!CAUTION]
> `#[WithEnvironmentVariable]` is not an exact replacement: PHPUnit neither populates nor
> backs up `$_SERVER`. Infection does not use `$_SERVER`, so this difference does not affect
> the project.

## Decision

Tests that exercise code using a statically identifiable environment variable through
`putenv()` or `$_ENV` must declare that variable with a class- or method-level PHPUnit
`#[WithEnvironmentVariable]` attribute:

```php
#[WithEnvironmentVariable('EXAMPLE_VARIABLE')]
final class ExampleTest extends TestCase
{
}
```

The rule applies to environment usage in the test class, its parent test cases, classes
referenced by PHPUnit coverage attributes, and their parents.

Environment-variable names are identified from literal `putenv()` arguments, literal name
prefixes such as `'EXAMPLE_VARIABLE=' . $value`, and literal `$_ENV['EXAMPLE_VARIABLE']`
accesses. No declaration is required when the name cannot be determined statically, such as
`putenv($name . '=value')` or `$_ENV[$name]`.

Use PHPUnit's native attribute instead of project-specific utilities for capturing and
restoring the environment.

## Consequences

- Each test documents the environment variables on which it depends.
- PHPUnit controls the initial value and restores the previous `getenv()` and `$_ENV` values.
- Tests no longer capture and restore the complete process environment.
- Environment usage in covered source code and inherited code participates in the check.
- Dynamically determined variable names are outside automated enforcement and require review.
- Direct `$_SERVER` environment manipulation is outside the scope of this decision.

## Enforcement

`PHPUnitTestsUsingEnvironmentVariablesShouldDeclareThemTest` runs through PHPStan as a
[PHPat][phpat] rule. Its PHP-Parser-based analyser collects statically identifiable names
from test and covered code. `PHPUnitTestMissingEnvironmentVariable` reports tests without
matching `WithEnvironmentVariable` declarations.

The analyser and selector have focused PHPUnit tests, including literal and dynamic
`putenv()` arguments, `$_ENV` accesses, inherited covered code, and correctly declared
variables.

## Status

Accepted.

[phpunit]: https://phpunit.de/
[phpat]: https://github.com/carlosas/phpat
[with-env-variable-doc]: https://docs.phpunit.de/en/12.5/attributes.html#withenvironmentvariable
