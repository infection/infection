# Debug events log

Enable the internal event trace with a file destination in Infection's configuration:

```json
{
    "logs": {
        "debugEvents": "var/events.jsonl"
    }
}
```

The path is relative to the Infection configuration file. Each JSONL record contains
`sequence`, the event's fully qualified class name in `event`, and its selected values
in `data`. A completed run replaces the previous log. Event names and payloads are
internal details and may change between versions.

The log is written by `DebugEventsDataProducer` at mutation-testing completion.
It records initial commands and output, processed source paths and mutation hashes,
mutation attributes, and completed mutant process results. It does not force lazy
mutation diffs, serialize ASTs, read coverage reports, or snapshot cache files.
A run that fails before reporting may leave no log or a previous run's log.

The PHPUnit TIA fixture combines this trace with existing artefacts:

| Evidence | Source |
| --- | --- |
| Initial PHPUnit output | `InitialTestSuiteWasFinished.data.outputText` |
| Generated mutation hashes | `MutableFileWasProcessed.data.mutationHashes` |
| Evaluated mutation identities and outcomes | `MutantProcessWasFinished.data.executionResult` |
| Mutation diffs and MSI | Infection's JSON report |
| Line-to-test coverage | Generated PHPUnit XML coverage |
| Loaded/executed test identities and effective configuration | The fixture's PHPUnit extension |
| Mutant configuration XML | Generated files retained by Infection's `--debug` option |
| Cache contents after the initial run | The fixture extension's shutdown snapshot |
| Cache contents after mutant execution | A snapshot taken by Behat when Infection exits |

The fixture preserves these values in immutable result objects before another run
can overwrite the files. It compares mutation semantics, coverage and MSI with TIA
disabled in the same project directory; timings and completion order are excluded.

Cache snapshots contain SHA-256 hashes of `test-impact-data` and `test-run-history`.
The initial snapshot runs at PHPUnit shutdown because recording is persisted after
its test-execution events. Mutant processes do not write that snapshot. Equal hashes
establish unchanged contents, not cache usability or why tests were selected.

Loaded tests are the suite inventory before filtering. Executed tests use PHPUnit's
`Test\Prepared` event; tests skipped or failing during preparation do not appear.
Covering tests and mutant execution results do not identify a particular killing test.
That requires test-framework output or additional instrumentation.
