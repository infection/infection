# Prefer Webmozart Assert for argument validation

## Context

[`webmozart/assert`][webmozart-assert], introduced on 24 July 2018 in
[PR #390][pr-390], is the established convention for argument validation in Infection.
A small number of guards, mostly older ones, still construct the base
`InvalidArgumentException` directly. This ADR records the existing convention.

Documenting and automatically enforcing the convention gives new contributors
and agents a clear default, reducing the need to infer it from mixed examples
or past reviews.

The automated rule needs to distinguish generic argument checks from domain
failures whose exception type matters to callers. Direct construction of the
base `InvalidArgumentException` identifies the former: as a `LogicException`,
it conventionally signals a violated precondition that should propagate rather
than be caught for recovery. Although PHP does not enforce that convention, it
provides a basis for reporting these constructions without interpreting each
guard. Subclasses are excluded because they may express domain contracts that
an assertion would not preserve.

## Decision

In production code, use `Webmozart\Assert\Assert` for argument validation that
would otherwise construct the base `InvalidArgumentException`. Choose the
assertion that expresses the constraint and provide a message explaining the
failure.

Preserve domain-specific subclasses: callers may depend on their type,
interfaces, or additional data. Other exception types are outside this
convention. Tests may construct exceptions directly as expected values or test
doubles.

Whether runtime validation is needed remains a matter of developer judgement
and is outside the scope of this ADR. When replacing a guard, preserve its
accepted inputs and failure message.

For example, replace this guard:

```php
if ($testFrameworkExtraArgs !== null) {
    throw new InvalidArgumentException(
        'Cannot combine positional test paths with extra test framework arguments.',
    );
}
```

with this assertion:

```php
Assert::null(
    $testFrameworkExtraArgs,
    'Cannot combine positional test paths with extra test framework arguments.',
);
```

Preserve a domain exception whose interface is part of the caller's contract:

```php
interface ConfigurationFailure extends Throwable {}

final class InvalidTestSelection extends InvalidArgumentException implements ConfigurationFailure {}

if ($testFrameworkExtraArgs !== null) {
    throw new InvalidTestSelection(
        'Cannot combine positional test paths with extra test framework arguments.',
    );
}
```

Replacing this exception with `Assert::null()` would prevent
`catch (ConfigurationFailure $error)` from handling the failure.

## Consequences

Named assertions replace repeated guards and expose type constraints to
PHPStan. Automated enforcement allows reviewers to focus on validation
correctness rather than reiterating style guidance.

Webmozart Assert throws a subclass of `InvalidArgumentException`. Catching the
base type still works, but code that depends on an exact class name requires
review before conversion. Domain-specific exceptions retain their existing
contracts.

## Enforcement

`InvalidArgumentExceptionRule`, registered in `devTools/phpstan.neon`, reports
direct construction of the base `InvalidArgumentException` under `src/`.
It excludes subclasses and code outside that directory. Fixture tests cover
imported, fully qualified, and aliased names, as well as a subclass with a marker
interface.

The rule identifies constructions to review; it neither chooses an assertion
nor proves that a replacement preserves behaviour. Existing violations remain
visible until reviewed and addressed.

## Status

Accepted.


[webmozart-assert]: https://packagist.org/packages/webmozart/assert
[pr-390]: https://github.com/infection/infection/pull/390
