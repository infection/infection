# Reusing recordings between PHPUnit and Infection

Infection generates a temporary PHPUnit XML configuration from the project's configuration,
adjusting settings for its initial test run. PHPUnit uses the directory of that XML to
identify the project ([`Application::baseDirectoryOf()`][application]).

A developer may run PHPUnit before running Infection. Infection should be able to reuse
that TIA recording to reduce its initial test run. Likewise, a later PHPUnit run should
be able to reuse the recording created by Infection. Both tools test the same project.

Reuse currently fails even when both tools use the same cache directory. PHPUnit describes
source paths relative to the XML directory when checking recording compatibility
([`Assumptions`][assumptions]). The same source directory becomes `src` from the project's
XML and `../../../../src` from Infection's generated XML. PHPUnit interprets this as a
change in what counts as first-party code and runs the full suite. Converting the XML paths
to absolute paths does not resolve this: PHPUnit makes them relative again for the check.

The [PHPUnit-to-Infection][sharing] and [Infection-to-PHPUnit][sharing-reverse] scenarios
both failed again on 2026-10-05 and are tagged `@skip`. They expect only `CalculatorTest`,
but both tests run. This prevents the optimisation; no tests are omitted.

The [unknown-source scenario](../../../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature#L35)
also falls back because the source definition changed, before reaching the unknown-path
check. Its explanation differs from the one expected, so it fails and is tagged `@skip`.
It does not currently isolate unknown-path fallback.

## Feedback for PHPUnit

Could a caller preserve the original project's recording base while running with generated
XML? Reusing a recording should remain possible when the source definition is unchanged.

Infection also needs to [honour the project's cache directory](../infection/project-cache.md).
Fixing that alone does not resolve the configuration-location mismatch described here.

[application]: https://github.com/sebastianbergmann/phpunit/blob/e21b72d4ac3a9d9351eaa065638dd5e6259879bd/src/TextUI/Application.php
[assumptions]: https://github.com/sebastianbergmann/phpunit/blob/e21b72d4ac3a9d9351eaa065638dd5e6259879bd/src/Runner/TestImpactAnalysis/Assumptions.php
[sharing]: ../../../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature#L111
[sharing-reverse]: ../../../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature#L126
