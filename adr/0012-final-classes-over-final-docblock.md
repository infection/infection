# Prefer `final` to `@final` where possible

## Context

Infection treats source classes as closed by default. Final classes make that
contract visible to the engine, static analysis, mutation testing, and readers
of the code.

Some services are still mocked by PHPUnit tests. Marking those classes with the
`final` keyword would break those tests even when the production design has not
changed. For such classes, the `@final` docblock records the architectural
intent while preserving PHPUnit mockability.

Using `@final` for classes that are not mocked weakens the signal. It makes the
class look intentionally mockable when there is no test suite reason for it, and
leaves the runtime inheritance contract looser than necessary.


## Decision

Use the `final` keyword for concrete source classes by default.

Use `@final` without the `final` keyword only when a source class needs to stay
mockable by PHPUnit tests.

The exception must remain tied to the test need. When a class stops being mocked,
replace `@final` with the `final` keyword.

Exceptional non-final classes that also cannot use `@final` need a dedicated
architecture rule with a reason. `ParallelProcessRunner` is the known exception:
it remains non-final only to allow PHPUnit partial mocks, and no class may
extend it.


## Enforcement

`tests/Architecture/PHPat/ClassesShouldBeFinalTest.php` enforces that concrete
source classes are either hard `final` or documented with `@final`, and that
`ParallelProcessRunner` is not extended. The rule intentionally accepts both
forms because mockability is a test-suite constraint reviewed at the class
level.


## Status

Accepted.
