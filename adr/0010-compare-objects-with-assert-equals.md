# Compare objects with PHPUnit `assertEquals()`

## Context

Tests often need to compare an object returned by the system under test with a separately
constructed expected object. In these cases, object identity is irrelevant; the test is
concerned only with whether the objects have the same state.

PHPUnit's `assertSame()` compares objects by identity, so it cannot express this expectation.
Comparing each property individually requires repetitive assertions or bespoke assertion
helpers. This approach adds boilerplate, exposes otherwise encapsulated state, and risks
omitting properties from the comparison. A helper can easily become incomplete when a new
property is added without a corresponding assertion.

Before StrictPHPUnit was introduced, tests used assertion helpers such as
`ConfigurationAssertions` and `LogsAssertions` to compare each property explicitly. These
helpers were verbose and had to remain synchronised with every change to the objects they
compared. For example, an abbreviated `LogsAssertions` used the following pattern:

```php
trait LogsAssertions
{
    private function assertLogsStateIs(
        Logs $logs,
        ?string $expectedTextLogFilePath,
        ?string $expectedHtmlLogFilePath,
        ?string $expectedSummaryLogFilePath,
        // ...one parameter for every other property
    ): void {
        $this->assertSame($expectedTextLogFilePath, $logs->getTextLogFilePath());
        $this->assertSame($expectedHtmlLogFilePath, $logs->getHtmlLogFilePath());
        $this->assertSame($expectedSummaryLogFilePath, $logs->getSummaryLogFilePath());
        // ...one assertion for every other property
    }
}
```

PHPUnit's `assertEquals()` compares the properties of two objects recursively and reports
any differences. However, it uses PHP's loose object comparison semantics. Values of
different types may therefore compare as equal, particularly for untyped or nullable
properties. Infection mitigates this risk through strict types, typed properties, and
StrictPHPUnit. The distinction nevertheless remains relevant when selecting an assertion.

This distinction led to the PHP-CS-Fixer [`php_unit_strict` rule][php-unit-strict] being
disabled in [#2486][2486]. [StrictPHPUnit][strict-phpunit] was subsequently introduced in
October 2025 in [#2487][2487]. It makes object property comparisons strict while retaining
the concise `assertEquals()` API.

## Decision

When a test expects two distinct objects to have equal state, compare them directly, with the
expected object first and the actual object second:

```php
$this->assertEquals($expectedObject, $actualObject);
```

This convention requires [StrictPHPUnit][strict-phpunit] to be installed and enabled for the
test suite. Do not use `assertEquals()` for object state equality without it, because
PHPUnit's loose scalar property comparison could conceal a type difference. If StrictPHPUnit
cannot be enabled, compare the properties explicitly or use another assertion that preserves
strict scalar comparison.

Use `assertSame()` when object identity is part of the contract. Continue to compare scalar
values with `assertSame()` or a more specific assertion. Use explicit property assertions or
a dedicated assertion helper when loose comparison could conceal a meaningful type
difference, or when the domain requires comparison semantics other than complete object
state equality.

## Consequences

- Tests express object state equality through one direct assertion.
- Tests remain independent of accessors added solely to expose internal state for
  assertions.
- New or changed object properties participate in the comparison automatically.
- Failures use PHPUnit's object difference output.
- StrictPHPUnit remains a required test dependency and PHPUnit extension for this convention.
- Authors must account for loose comparison semantics when an object's property types make
  the comparison ambiguous.

## Enforcement

This convention is enforced during review. `composer.json` retains StrictPHPUnit as a test
dependency, and the PHPUnit configurations register its extension. Removing or disabling it
requires replacing these object equality assertions or providing an equivalent strict
comparator. The [PHP-CS-Fixer][php-cs-fixer] `php_unit_strict` rule remains disabled because
it would replace object equality assertions with identity assertions.

## Alternatives considered

Using `assertSame()` for every comparison was rejected in [#2486][2486] because distinct
objects with equal state are not identical.

Comparing each property individually, directly or through a bespoke helper, was rejected as
the default because it is verbose, couples tests to the object's representation, and can
omit relevant state, particularly when properties are added later. Dedicated helpers remain
appropriate when the domain requires custom comparison semantics or clearer failure
diagnostics.

## Status

Proposed.

[2486]: https://github.com/infection/infection/pull/2486
[2487]: https://github.com/infection/infection/pull/2487
[php-unit-strict]: https://cs.symfony.com/doc/rules/php_unit/php_unit_strict.html
[strict-phpunit]: https://github.com/webmozarts/strict-phpunit
[php-cs-fixer]: https://github.com/FriendsOfPHP/PHP-CS-Fixer
