# Tracking the project's dependency lock with generated XML

Infection uses the system temporary directory when `tmpDir` is not configured, so generated
PHPUnit XML outside the project is a normal configuration. If the developer updates the
project's dependencies, PHPUnit should invalidate the TIA recording because dependency
changes can affect any test.

PHPUnit searches for `composer.lock` by walking upwards from the generated XML directory
([`Assumptions`][assumptions]). When that directory is outside the project, the search can
find another project's lock file or no lock file at all. Changes to the actual project's
lock then go unnoticed, and PHPUnit continues using the old recording.

The [external-XML scenario][external-xml] creates a recording, edits the scenario project's
lock file and runs Infection again. Both runs use the same external XML location. PHPUnit
tracks the fixture root's lock file instead, so the scenario fails and is tagged `@skip`.
The corresponding lock-change example with XML inside the project passes. The edit only
adds whitespace: it verifies which file is tracked, without changing installed dependencies.

## Feedback for PHPUnit

Could a caller identify the project's lock file independently of the generated XML location?
The recording needs to track the dependencies of the project being tested.

[assumptions]: https://github.com/sebastianbergmann/phpunit/blob/e21b72d4ac3a9d9351eaa065638dd5e6259879bd/src/Runner/TestImpactAnalysis/Assumptions.php
[external-xml]: ../../../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature#L95
