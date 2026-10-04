# Execution report

The execution report exposes evidence for end-to-end checks of test selection and
mutation testing. Enable it with a file destination in Infection's configuration:

```json
{
    "logs": {
        "execution": "var/execution.jsonl"
    }
}
```

The path is relative to the Infection configuration file. Stream destinations are
not supported. The report uses the `DataProducer`, `ComposableReporter`, and
`FileWriter` APIs under `src/Report/`.

Each line is a JSON object with an `event` field. The `execution_started` record
contains `formatVersion: 1`. A new execution replaces the previous report.
Snapshots are written at application start, initial-test start, initial-test
completion, and mutation-testing completion. Source and mutant records are collected
without rewriting the file for every mutant. Initial-test evidence remains available
when the initial suite fails; an interrupted mutation phase can leave a partial report
without a `mutation_testing_finished` record.

| Record | Evidence |
| --- | --- |
| `initial_tests_started` | Initial command, generated PHPUnit XML, explicit impact-query file contents, configured cache hashes before the initial run |
| `initial_tests_finished` | Initial output, JUnit test identities and skipped markers, configured cache hashes after the initial run |
| `source_processed` | Source path, generated mutation hashes, source lines and their covering test identities from Infection's trace |
| `mutant_finished` | Mutation hash, source path, mutator, line range, diff, detection status, covering tests, command, output, generated PHPUnit XML |
| `mutation_testing_finished` | Configured cache hashes after mutant execution |

## Evaluating TIA scenarios

| Claim | Check |
| --- | --- |
| Only CalculatorTest ran initially | Compare non-skipped `testCases` identities, including class and method/data-set name |
| An empty selection is legitimate | Require an available JUnit report whose `testCases` is empty; `null` means unavailable or invalid XML |
| Coverage is preserved | Compare `source_processed.coverage` for the selected files between TIA and disabled runs |
| The same mutations are generated and detected | Compare source path, mutator, line range, diff, and status; include generated hashes to account for mutations without an execution result |
| MSI is 100 percent | Use the existing `logs.json` or `logs.summaryJson` statistics; the execution report does not recalculate metrics |
| The Calculator Plus mutant is killed | Find its `mutant_finished` record and require status `killed by tests` |
| A recording is reusable | Run again without changing the project and verify that the unrelated initial test is omitted |
| Mutants leave TIA data intact | Compare the initial-finished and mutation-finished `configuredCache` snapshots, and inspect mutant commands and XML for selection/recording settings |
| A fallback or subset is explained | Inspect initial PHPUnit output and Infection's console output; cache hashes alone do not establish why selection happened |

## Interpretation limits

JUnit entries with a skipped marker do not establish that their test body ran.
`coveringTests` describes the tests associated with a mutation, not the tests observed
executing in its subprocess or the test that killed it. Identifying a particular
killing test still requires framework output or additional framework instrumentation.
The command runner still supplies the overall exit status. The finished-mutant event exposes the final process result, not every subprocess in
a static-analysis follow-up chain.

Cache snapshots contain SHA-256 hashes of `test-impact-data` and `test-run-history`.
They use the cache directory from the generated initial PHPUnit XML. A CLI override
of that directory is visible in the command but is not followed by the snapshot.
A missing or unreadable cache file has a `null` hash; no configured directory gives
`configuredCache: null`. Equal hashes establish unchanged contents, not cache usability.

PHPUnit XML, impact-query, and JUnit fields can be unavailable for other test frameworks.
Coverage describes processed source files and covering tests, not every executable
line or the whole project. Paths and hashes can differ between isolated copies of a
fixture; normalize paths before comparing reports. Ignore test timing and completion
order when comparing semantics. Process output with invalid UTF-8 uses replacement
characters so it can still be encoded as JSON.
