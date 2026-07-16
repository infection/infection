# Declare PHPUnit coverage metadata explicitly

## Context

PHPUnit can attribute executed code to a test automatically, but that makes the
reported coverage depend on every implementation detail the test exercises.
Incidental collaborators may then appear to be covered even though the test does
not deliberately exercise their contract.

Explicit coverage metadata documents the subject of a test and limits its
coverage contribution to that subject.

PHP-CS-Fixer can also add the `#[CoversNothing]` attribute when no PHPUnit
coverage attribute is present.

Explicit coverage attributes also make it easier to determine programmatically
which tests cover which production code, simplifying the creation and
enforcement of PHPat rules.

The question is which convention to enforce.

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
test subject. Outside the AutoReview suite and PHPat selector fixtures, a
test that uses it must belong to the `integration` group.

Each test must declare at least one attribute.

## Enforcement

`phpunit.xml.dist` and `phpunit_autoreview.xml` set
`requireCoverageMetadata="true"`. Together with `failOnRisky="true"`, this makes
missing metadata fail the test suite.

`tests/Architecture/PHPat/PHPUnitTestsWithCoversNothingShouldBelongToIntegrationGroupTest.php`
enforces the `integration` group for `#[CoversNothing]`, with explicit exemptions
for AutoReview tests and PHPat selector fixtures.

PHP-CS-Fixer adds `#[CoversNothing]` when no coverage attribute is present.

The I/O architecture rules resolve `#[CoversClass]`, `#[CoversTrait]`, and
`#[CoversMethod]` through
`tests/Architecture/PHPat/Selector/Support/PHPUnitTestClassAnalysis.php`.

## Status

Accepted.
