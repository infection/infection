# Define the public API through an extension-point registry

## Context

Most classes under `src/` must be accessible so that Infection's components can collaborate,
but this does not make them supported extension points. Treating every accessible class as
public API would expose implementation details and turn routine refactoring into a
backwards-compatibility concern.

Infection provides a small set of contracts on which users may depend when extending or
integrating with the project. These contracts require a deliberate stability commitment and
sufficient documentation to be usable without relying on internal implementation details.

PHP does not prevent consumers from using internal classes. The `@internal` docblock marks
the support boundary for users, static analysis and maintainers, while an explicit registry
identifies the exceptions.

The convention was introduced in [#339][339], which added the extension-point registry and
AutoReview checks requiring `@internal` on non-extension-point source classes and prohibiting
it on extension points. [#753][753] later extracted the registry into
`ProjectCodeProvider`. [#3232][3232] moved the checks from PHPUnit AutoReview tests to PHPat.

## Decision

Treat every class under `src/` as internal by default and mark it with `@internal`.

The public API consists only of the extension points listed in
`ProjectCodeProvider::EXTENSION_POINTS`. An extension point must not be marked `@internal`
and must have a PHP docblock documenting its contract for users.

Adding an extension point to the registry is a deliberate expansion of the supported public
API. Removing an extension point or changing its contract incompatibly constitutes a
backwards-compatibility change and must be handled accordingly. Other source classes may
change without a backwards-compatibility guarantee.

## Consequences

- Users have one explicit list of supported extension points.
- Maintainers can refactor internal classes without treating their implementation details as
  public API.
- Extension points carry stronger compatibility and documentation obligations.
- Adding a new extension point requires an explicit registry change rather than merely
  omitting `@internal`.
- The `@internal` marker communicates intent but cannot prevent consumers from depending on
  unsupported classes.

## Enforcement

`tests/phpunit/AutoReview/ProjectCode/ProjectCodeProvider.php::EXTENSION_POINTS` is the single
source of truth for the public API registry.

`tests/Architecture/PHPat/SourceClassesShouldBeInternalTest.php` enforces that non-extension
points are marked `@internal` and extension points are not.

`tests/Architecture/PHPat/ExtensionPointsShouldHaveDocBlockTest.php` requires each extension
point to have a PHP docblock. Both PHPat rules run as part of PHPStan.

## Alternatives considered

Treating every accessible source class as public API was rejected because it would expose
implementation details and make internal refactoring a backwards-compatibility concern.

Marking every source class `@internal` without exceptions was rejected because users need
stable contracts for supported extensions and integrations.

Inferring the public API from the absence of `@internal` without maintaining a registry was
rejected because accidental omissions could silently expand the supported API.

## Status

Accepted.

[339]: https://github.com/infection/infection/pull/339
[753]: https://github.com/infection/infection/pull/753
[3232]: https://github.com/infection/infection/pull/3232
