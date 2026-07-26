# Prefer `final` to `@final` where possible

## Context

Infection treats source classes as closed by default. Declaring a class `final`
makes this contract explicit to the engine, static analysis, mutation testing,
and readers.

Some services still need to be mocked in PHPUnit tests. Declaring these classes
`final` would break those tests even though the production design remains
unchanged. For these classes, the `@final` docblock records the architectural
intent while preserving their mockability.

## Decision

Use the `final` keyword for concrete source classes by default.

Use `@final` without the `final` keyword only when PHPUnit tests need to mock a
source class.

When a class stops being mocked, replace `@final` with the `final` keyword.


## Alternatives considered

Tools such as `dg/bypass-finals` can remove the `final` restriction during test
execution. Infection does not use this approach because tests would exercise a
different inheritance contract from production. Classes that need to remain
mockable use `@final` instead.


## Enforcement

`tests/Architecture/PHPat/ClassesShouldBeFinalTest.php` enforces that concrete
source classes either use the `final` keyword or are documented with `@final`.
The rule accepts both forms because mockability is a test-suite constraint that
is reviewed for each class.


## Status

Accepted.
