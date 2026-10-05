# Continuing without TIA when its cache is unusable

TIA should reduce the initial test run without becoming a requirement for mutation testing.
If its cache cannot be used, Infection should run the eligible full suite and continue
without automatic TIA.

Currently, a file at the cache-directory path or a directory at the recording-file path
prevents that fallback. Both examples in the [cache-obstruction outline][cache-obstruction]
fail and are tagged `@skip`.

With the cache-directory obstruction, no configured cache is reported. The recording-file
obstruction emits `fopen(...): Is a directory`, and the observed initial process exits 143.
Neither case completes the requested fallback. These scenarios test path types rather than
Unix permissions and do not cover every filesystem failure.

Infection needs to handle cache unavailability. The related
[feedback for PHPUnit](../phpunit/cache-write-errors.md) concerns how write failures are
reported to callers. Missing, empty and version-incompatible *recordings* already trigger
fallback and are replaced with reusable data; these failures concern storage itself.

[cache-obstruction]: ../../../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature#L167
