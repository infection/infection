# Changed tests can be missed when selecting tests for a source file

Infection asks PHPUnit to run the tests covering a selected source file. PHPUnit identifies
those tests from its previous recording. It can therefore miss an existing test that has
since changed to cover that file.

For example:

1. Run `infection src/Calculator.php` with no previous TIA recording. Its initial test run
   executes the full suite and records which source files each test executes.
   `CalculatorTest` covers `Calculator.php`; `UnrelatedTest` covers only `Unrelated.php`.
2. Edit `UnrelatedTest` to also call and test `Calculator`, keeping the same test name.
3. Run `infection src/Calculator.php` a second time. Infection passes the source path to
   PHPUnit through `--impacted-by-file`.
4. PHPUnit runs only `CalculatorTest`. The recording still associates `UnrelatedTest`
   only with `Unrelated.php`, so PHPUnit omits it despite its new coverage of `Calculator.php`.

On this second Infection run, the initial test run collects coverage without executing
`UnrelatedTest`, even though it now covers `Calculator.php`. Infection can therefore miss
covering tests or covered lines. The mutation score may still match a full run if another
test kills the same mutants; that does not show that the selection is safe.

PHPUnit's explicit query explains this behaviour: `--impacted-by-file` supplies the files
to treat as changed. PHPUnit looks up their recorded dependencies without also checking
which tests or inputs have changed since the recording. Infection supplies the files
selected for mutation, which may be unchanged, and omits changed tests and inputs.

`--only-impacted` checks for changes since the recording, but adding it to an explicit
query does not combine the two selections ([`Selector::explain()`][selector]). Using it
alone would also be insufficient: Infection needs coverage for its selected source files
even when those files have not changed.

Infection needs to include both the tests recorded as covering its selected source files
and tests that may have become relevant since the recording. If it cannot do that safely,
it needs to run the full suite. The current Git diff alone would not solve this: it may
cover a different period from the changes since the recording.

This is a requirement for Infection's integration. PHPUnit's explicit query is behaving
according to its contract.

| Behat reproduction | What it establishes |
| --- | --- |
| [02: An existing test starts covering another source file][changed-test] | The edited test now covers `Calculator.php` but is not selected. |
| [02: Changed data-provider inputs establish a previously unknown dependency][changed-provider] | A changed provider makes the test exercise `Calculator`, but the test is not selected. Its data-set ID stays the same. |
| [04: A changed external fixture remains relevant to an explicit impact query][changed-fixture] | A test declaring `UsesFixture` is not selected after its JSON input changes. |

All three scenarios are tagged `@skip` and run through the `blocked` profile. The first two
use observed-execution recording and fail at the test-selection assertion before comparing
coverage and mutation results with a full run. They do not verify the declared-target strategy.
The fixture scenario adds an unused JSON field. It demonstrates that the changed input is
ignored, but does not demonstrate lost coverage or different test results.

## Feedback for PHPUnit

Can explicit source queries also account for tests and inputs changed since the recording,
without losing tests that cover unchanged mutation targets? Otherwise Infection needs a
conservative full-suite fallback.

## Inspect the selection

After a changed-test/provider failure, compare selection without executing tests from its
generated scenario directory:

```sh
XDEBUG_MODE=coverage php vendor/bin/phpunit \
    --configuration var/infection/tmp/infection/phpunitConfiguration.initial.infection.xml \
    --impacted-by-file var/infection/tmp/infection/phpunit-impact-sources.txt --explain-impacted
XDEBUG_MODE=coverage php vendor/bin/phpunit \
    --configuration var/infection/tmp/infection/phpunitConfiguration.initial.infection.xml \
    --only-impacted --explain-impacted
```

[selector]: https://github.com/sebastianbergmann/phpunit/blob/e21b72d4ac3a9d9351eaa065638dd5e6259879bd/src/Runner/TestImpactAnalysis/Selector.php
[changed-test]: ../../../tests/e2e/PHPUnit_TIA/features/02-development-cycle.feature#L39
[changed-provider]: ../../../tests/e2e/PHPUnit_TIA/features/02-development-cycle.feature#L69
[changed-fixture]: ../../../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature#L138
