# Declare PHPUnit coverage metadata explicitly

## Context

PHPUnit can attribute executed code to a test automatically, but that makes the
reported coverage depend on every implementation detail the test exercises.
Incidental collaborators may then appear to be covered even though the test does
not deliberately exercise their contract.

Explicit coverage metadata documents the subject of a test and limits its
coverage contribution to that subject.

[PHP-CS-Fixer][php-cs-fixer] can also add the `#[CoversNothing]` attribute when
no PHPUnit coverage attribute is present.

Explicit coverage attributes also make it easier to determine programmatically
which tests cover which production code, simplifying the creation and
enforcement of [PHPat][phpat] rules.

**The question is which convention to enforce.**

Historically, ADR 0002 (now deprecated and deleted) stated that:

- The `@covers` annotation was not required.
- The [`Symfony\Bridge\PhpUnit\CoverageListener`][code-coverage-listener] from
  [`symfony/phpunit-bridge`][phpunit-bridge] was configured in
  `phpunit.xml.dist`, providing its benefits without requiring `@covers`
  annotations.
- [PHP-CS-Fixer][php-cs-fixer] should not remove `@covers` annotations.

Since [PR #347], [PHP-CS-Fixer][php-cs-fixer] has enforced the
`php_unit_test_class_requires_covers` rule, which adds a covers-nothing
annotation, now an attribute, to test classes without coverage metadata.

However:

- As of PHPUnit 11, attributes have replaced `@covers` annotations.
- The `Symfony\Bridge\PhpUnit\CoverageListener` was removed in [PR #1926]
  because it was incompatible with the planned upgrade to PHPUnit 10 and 11 at
  the time.

The requirement for tests using `#[CoversNothing]` to belong to the
`integration` group is a project convention, not an inference that
`#[CoversNothing]` inherently indicates I/O. [PR #3277] reviewed the limited
class-level usage of `#[CoversNothing]` and found that all such tests belonged to
the `integration` group. The project retained this established convention and
separated its enforcement from the I/O architecture rules.

This document supersedes `adr/0002-@covers-annotations.md` and reflects the
current convention.

## Decision

Every concrete PHPUnit test class must declare coverage metadata using PHPUnit
attributes.

Use the narrowest attribute that describes the test's intended subject:

- `#[CoversClass]` for a class.
- `#[CoversTrait]` for a trait.
- `#[CoversMethod]` when the intended subject is one method.
- `#[CoversFunction]` for a function.
- `#[CoversNothing]` when the test has no meaningful production symbol to cover.

A test may declare multiple coverage attributes when it deliberately covers
multiple subjects. It must not list incidental collaborators merely because the
test executes them.

`#[CoversNothing]` is an explicit exception, not a substitute for identifying the
test subject. Outside the AutoReview suite and [PHPat][phpat] selector fixtures, a
test that uses it must belong to the `integration` group.

Each test must declare at least one attribute.

## Enforcement

`phpunit.xml.dist` and `phpunit_autoreview.xml` set
`requireCoverageMetadata="true"`. Together with `failOnRisky="true"`, this makes
missing metadata fail the test suite.

`tests/Architecture/PHPat/PHPUnitTestsWithCoversNothingShouldBelongToIntegrationGroupTest.php`
enforces the `integration` group for `#[CoversNothing]`, with explicit exemptions
for AutoReview tests and [PHPat][phpat] selector fixtures.

[PHP-CS-Fixer][php-cs-fixer] adds `#[CoversNothing]` when no coverage attribute
is present.

The I/O architecture rules resolve `#[CoversClass]`, `#[CoversTrait]`, and
`#[CoversMethod]` through
`tests/Architecture/PHPat/Selector/Support/PHPUnitTestClassAnalysis.php`.

## Status

Accepted.


[code-coverage-listener]: https://symfony.com/doc/current/components/phpunit_bridge.html#code-coverage-listener
[phpunit-bridge]: https://packagist.org/packages/symfony/phpunit-bridge
[php-cs-fixer]: https://github.com/FriendsOfPHP/PHP-CS-Fixer
[phpat]: https://github.com/carlosas/phpat
[PR #347]: https://github.com/infection/infection/pull/347
[PR #1926]: https://github.com/infection/infection/pull/1926
[PR #3277]: https://github.com/infection/infection/pull/3277
