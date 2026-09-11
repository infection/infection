# Name exceptions after domain failures

## Context

An exception's name is part of the vocabulary used by callers, documentation and error
handling. A generic name such as `FinderException` identifies the component that threw it,
but does not identify what went wrong. Adding an `Exception` suffix only repeats information
already expressed by the class hierarchy.

Infection already has concrete exception names that describe the failure, including
`NoSourceFound`, `InvalidXml`, `MinMsiCheckFailed` and `CouldNotResolveBuildContext`. These
names remain meaningful at throw and catch sites without relying on their namespace or
inheritance to explain the condition.

Exceptions also participate in two distinct classifications. Their SPL parent communicates
the kind of programming or runtime failure, while a domain marker interface can group
otherwise unrelated failures that callers need to catch together. Neither classification
should force concrete classes to share a generic name.

Some existing first-party exceptions, such as `FinderException`, predate this convention.
Renaming them solely to make the existing tree uniform would create churn and may affect
consumers without improving current behaviour.

## Decision

Name each new or renamed first-party concrete exception after the domain failure or invalid
condition it represents. Use names such as `FileNotFound`, `InvalidSchema` or
`InitialTestsFailed`. Do not add the `Exception` suffix.

The name must describe the exception's contract, not the implementation that detects or
throws it. When one class would need a generic component name because it represents several
unrelated failures, prefer separate, failure-specific exception classes. A single class may
still expose several named constructors when they represent variants of the same failure.

Give each exception a domain-specific concrete class, and extend the SPL exception type that
best categorises the failure. For example, `InvalidSchema` extends
`UnexpectedValueException`. The concrete name describes what failed; the parent type
describes the general kind of failure.

When callers need to catch several domain failures together, introduce a marker interface
that extends `Throwable` and name it after that catchable category, for example
`ReportLocationThrowable`. Group such interfaces in a `Throwable` namespace when a
subsystem has several throwable contracts. A concrete exception keeps its failure-specific
name when it implements the marker.

This decision applies to first-party production exceptions created or renamed after this
record. It does not require existing exceptions to be renamed as unrelated work, and it does
not control names imposed by third-party contracts or test fixtures.

## Consequences

- Throw and catch sites state the failure that code handles.
- Exception names remain useful when imported outside their original namespace.
- Concrete exception names do not repeat type information.
- Catching by failure category remains possible through SPL parents and domain marker
  interfaces.
- Some legacy exception names remain inconsistent until a justified change can rename them
  safely.

## Enforcement

PHPat enforces two structural parts of this decision for production exceptions:

- `ExceptionsShouldNotHaveExceptionSuffixTest` rejects concrete exception names ending with
  `Exception`.
- `ExceptionsShouldNotExtendExceptionDirectlyTest` rejects concrete exceptions whose
  immediate parent is `Exception`. They must extend a more specific SPL exception type.

PHPat reports legacy and new violations alike. Known legacy violations may be recorded in
the PHPStan baseline. Any new violation fails `make autoreview`.

Whether a concrete name accurately describes the domain failure, and whether its SPL parent
has the correct semantics, remain review decisions.

## Alternatives considered

Suffixing every concrete class with `Exception` was rejected because the suffix repeats the
class hierarchy and encourages component-oriented names such as `FinderException` instead
of describing the failure.

Using only SPL exception classes was rejected because a parent such as `RuntimeException`
does not express the domain condition and cannot distinguish failures that need different
handling.

Renaming all existing exceptions immediately was rejected because it would expand a naming
decision into an unrelated compatibility migration.

## Status

Accepted.
