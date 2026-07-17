# Compare objects with PHPUnit `assertEquals()`

## Context

Tests often need to compare an object returned by the system under test with a separately
constructed expected object. In these cases, object identity is irrelevant: the test is
concerned with whether the objects have the same state.

PHPUnit's `assertSame()` compares objects by identity, so it cannot express this expectation.
Comparing every property individually requires repetitive assertions or bespoke assertion
helpers. This adds boilerplate, exposes otherwise encapsulated state and risks omitting a
property from the comparison. Such a helper can easily become incomplete when a property is
added without a corresponding assertion.

Before StrictPHPUnit was introduced, tests used assertion helpers such as
`ConfigurationAssertions` and `LogsAssertions` to compare each property explicitly. These
helpers were verbose and had to remain synchronised with every change to the objects they
compared. For example, an abbreviated `LogsAssertions` followed this pattern:

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
their differences. However, it uses PHP's loose object comparison semantics. Values with
different types may therefore compare as equal, particularly for untyped or nullable
properties. Infection mitigates this risk through strict types, typed properties and
StrictPHPUnit, but the distinction remains relevant when choosing an assertion.

This distinction led to the PHP-CS-Fixer [`php_unit_strict` rule][php-unit-strict] being
disabled in [#2486][2486]. [StrictPHPUnit][strict-phpunit] was subsequently introduced in
October 2025 through [#2487][2487]. It makes object property comparisons strict while
retaining the concise `assertEquals()` API.

## Decision

When a test expects two distinct objects to have equal state, compare them directly with the
expected object first and the actual object second:

```php
$this->assertEquals($expectedObject, $actualObject);
```

Use `assertSame()` when object identity is part of the contract. Continue to compare scalar
values with `assertSame()` or a more specific assertion. Use explicit property assertions or
a dedicated assertion helper when loose comparison could conceal a meaningful difference
in type, or when the domain requires comparison semantics other than complete object state
equality.

## Consequences

- Tests express object state equality with one direct assertion.
- Tests remain independent of accessors added solely to expose internal state for
  assertions.
- New or changed object properties participate in the comparison automatically.
- Failures use PHPUnit's object difference output.
- Authors must account for loose comparison semantics when an object's property types make
  the comparison ambiguous.

## Enforcement

This convention is enforced during review. The [PHP-CS-Fixer][php-cs-fixer]
`php_unit_strict` rule remains disabled because it would replace object equality assertions
with identity assertions.

## Alternatives considered

Using `assertSame()` for every comparison was rejected in [#2486][2486] because distinct
objects with equal state are not identical.

Comparing each property individually, directly or through a bespoke helper, was rejected as
the default because it is verbose, couples tests to the object's representation and can omit
relevant state, particularly when properties are added later. Dedicated helpers remain
appropriate when the domain requires custom comparison semantics or clearer failure
diagnostics.

## Status

Proposed.

[2486]: https://github.com/infection/infection/pull/2486
[2487]: https://github.com/infection/infection/pull/2487
[php-unit-strict]: https://cs.symfony.com/doc/rules/php_unit/php_unit_strict.html
[strict-phpunit]: https://github.com/webmozarts/strict-phpunit
[php-cs-fixer]: https://github.com/FriendsOfPHP/PHP-CS-Fixer
