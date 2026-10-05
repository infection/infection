# Reporting impact-cache write failures to callers

Infection enables TIA as an optimisation. If the cache cannot retain a recording, Infection
needs enough information to continue mutation testing without automatic TIA.

The [cache-obstruction scenarios][cache-obstruction] exercise a file at the cache-directory
path and a directory at the recording-file path. The latter emits
`fopen(...): Is a directory`; the observed initial process exits 143. These scenarios test
obstructions caused by path types. They do not test Unix permissions or every filesystem failure.

## Feedback for PHPUnit

Could cache-write failures provide a clear diagnostic to help callers decide how to fall
back? The current reproduction does not establish the best API or exit status.

Infection's required [fallback behaviour](../infection/cache-fallback.md) is a separate
integration gap. Both Behat examples fail and are tagged `@skip`; they currently do not
complete the requested fallback without TIA.

[cache-obstruction]: ../../../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature#L167
