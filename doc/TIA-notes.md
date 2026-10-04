## Scenarios

We have two scenarios to consider and test properly:

- A project that does not have TIA enabled.
- A project that has TIA enabled.

For both cases, we can consider the following scenarios:

- A single run (e.g. CI).
- An initial run, an escaped mutation is reported, the user updates the tests, execute infection again.
- An initial run, the user adds some code (source or tests) and execute infection again.

The active Behat features cover projects with TIA enabled and automatic activation
when the project has no explicit TIA settings.

## Execution evidence

The `logs.execution` report provides initial test identities, line-to-test coverage,
mutation results, commands, generated PHPUnit XML, and configured-cache snapshots.
See [the report contract and scenario checks](execution-report.md).

A cache file or recording setting does not prove reuse: verify it with a subsequent
warm run. A killed mutation does not identify its killing test: assert its detection
status unless a scenario specifically needs framework failure output.

## Implemented initial-run scenarios

Behat runs all five features under `tests/e2e/PHPUnit_TIA/features/`. Confirmed
blockers retain their desired assertions under `@skip`; the `blocked` profile runs them.
The cold-start scenario passes: the first Infection run executes both tests, and the
second executes CalculatorTest only with identical Calculator coverage and mutation results.

### Project-configured cache is not reused

The PHPUnit-seeded scenario is tagged `@skip` and excluded from the Behat suite until
this blocker is resolved. `InitialConfigBuilder` replaces the project-configured
cache directory with `.infection/phpunit`, so the initial run executes both tests instead
of reusing the recording produced by plain PHPUnit. This is an Infection integration
blocker, not evidence of PHPUnit invalidating a shared recording.

## Implemented development-cycle scenarios

Five scenarios pass with the pinned PHPUnit build:

- Strengthening an existing test changes a Plus mutant from escaped to killed.
- A new test runs immediately and remains selected on the next unchanged run.
- A new source file triggers a full-suite fallback; the next run selects only its test.
- Editing Calculator to add an executed branch refreshes coverage and mutation results.
- Selecting Calculator, then Unrelated, then Calculator retains both sets of dependencies.

Each scenario compares the updated project's line-to-test coverage, generated mutation
hashes, evaluated mutations and detection statuses, and MSI against a full-suite run
with impact recording and automatic impact selection disabled. Both runs use the same
project directory; snapshots preserve results and avoid path/hash normalization.

Two scenarios retain their desired assertions under `@skip`, for the limitation below.

### Explicit impact queries miss changed tests

Reproduced with PHPUnit PR #6919 at `5f4f80f15f1bb1907e6ba277f2cec0756fbb8046`.
Infection passes the selected source paths through `--impacted-by-file`. PHPUnit queries
the recorded dependencies of those paths without also detecting changed tests.

Both scenarios start with a cold Infection run that executes CalculatorTest and
UnrelatedTest and records dependencies from executed code. Then they keep the existing
test identity but change how UnrelatedTest executes:

| Scenario | Edit | Expected initial tests | Actual initial tests |
| --- | --- | --- | --- |
| An existing test starts covering another source file | Add a Calculator assertion to UnrelatedTest | CalculatorTest and UnrelatedTest | CalculatorTest only |
| Changed data-provider inputs establish a previously unknown dependency | Change the same named data set from `[Unrelated::class, 2]` to `[Calculator::class, 3]` | CalculatorTest and UnrelatedTest's existing data set | CalculatorTest only |

For both updated projects, a direct PHPUnit `--impacted-by-file ... --explain` reports
only CalculatorTest. `--only-impacted --explain` instead reports the changed UnrelatedTest.
A full Infection run with `--test-framework-options=--do-not-record-test-impact-data`
executes both tests, and Calculator's coverage includes UnrelatedTest. The TIA run's
coverage omits it. The Plus mutant is killed in both runs by the existing coverage, so
equal MSI alone would hide this problem.

The provider scenario declares both classes as coverage targets before recording, but
derivation from targets is disabled. Its data-set name stays `selected calculator`;
changing the identity would exercise the already-working fallback for unknown tests.

This is a missing capability for Infection's integration, rather than a violation of
PHPUnit's explicit-query contract. `Runner/TestImpactAnalysis/Selector::explain()` uses
`testsThatDependOnAnyOf()` for explicit paths and `testsAffectedByWhatChanged()` only
when no explicit paths were supplied. Infection needs the union of dependencies on its
selected source files and tests whose changed code or inputs may establish new dependencies.
Using `--only-impacted` alone cannot provide coverage for unchanged selected source files.
Feedback for PHPUnit: could explicit queries optionally include changed-test and
data-provider safeguards? Until that is available, Infection needs a conservative fallback
before enabling this optimization generally.

To reproduce the desired assertions (two expected failures):

```sh
cd tests/e2e/PHPUnit_TIA
php vendor/bin/behat --xdebug --profile=blocked features/02-development-cycle.feature
```

After a failed scenario, run the following from its generated directory under
`var/behat/scenarios/02-development-cycle-*/` to compare PHPUnit's explanations:

```sh
XDEBUG_MODE=coverage php vendor/bin/phpunit \
    --configuration var/infection/tmp/infection/phpunitConfiguration.initial.infection.xml \
    --impacted-by-file var/infection/tmp/infection/phpunit-impact-sources.txt --explain
XDEBUG_MODE=coverage php vendor/bin/phpunit \
    --configuration var/infection/tmp/infection/phpunitConfiguration.initial.infection.xml \
    --only-impacted --explain
```

## Mutant isolation, cache reuse, and configuration

Features `03` through `05` now exercise the pinned build with the same execution
snapshots and explicit outcomes as the initial-run and development-cycle features.

Passing cases establish that:

- Observed-execution and declared-target recordings both survive mutant execution.
  Mutant commands and XML do not enable impact recording or selection. Scoped PHPUnit
  recordings show that each mutant loads and executes only CalculatorTest. Shared impact-data and test-history
  hashes remain unchanged. A subsequent warm run still selects CalculatorTest only.
  This fixture has one covering test; it does not establish ordering among multiple tests.
- Missing, empty, and incompatible recordings cause an explained full-suite fallback
  and are replaced with data that narrows the next run. An unrecorded queried source
  also causes a full-suite fallback.
- Changing an execution setting in XML, the bootstrap script, or `composer.lock`
  invalidates an existing recording when generated XML remains within the project.
- Plain PHPUnit and Infection accept each other's recordings when both explicitly use
  `.infection/phpunit`. Their differing report paths and presentation settings do not
  prevent reuse. This isolates the cache-directory override blocker in feature `01`.
- Without explicit TIA settings, Infection records observed execution and reuses it.
  CLI recording/history opt-outs work. Declared-target recording requires complete
  coverage metadata, and declarations do not make unexecuted lines eligible for mutation.
- Explicit filter, group, and suite restrictions remain effective. Scenarios tagged
  `@current_behavior` record today's suppression of automatic TIA; that policy is not settled.

### PHPUnit feedback: expose impact selection in the merged configuration

In the pinned PHPUnit build, `--only-impacted`, `--impacted-by`, and
`--impacted-by-file` are available through the CLI configuration but absent from
`PHPUnit\TextUI\Configuration\Configuration`, which extensions receive in `bootstrap()`.
Our [recording extension](../tests/e2e/PHPUnit_TIA/phpunit/RecordExecutionExtension.php)
therefore reparses `$_SERVER['argv']` using `PHPUnit\TextUI\CliArguments\Builder`
to record these settings alongside the effective configuration.

It would be useful to expose the effective impact-selection mode and inputs on the
merged configuration too. Extensions could then inspect and report them through
the configuration they already receive, without reparsing the command line or depending
on the CLI parser. This request concerns configuration introspection; it does not
require adding XML equivalents for these options.

### PHP runtime options are not fingerprinted

`04` records both tests, then repeats Infection with
`--initial-tests-php-options="-d precision=15"`. PHPUnit still selects CalculatorTest
only; it does not invalidate the recording. `ExecutionSettings::from()` describes
settings from PHPUnit's merged configuration, not arbitrary interpreter settings.
This is a missing safeguard for changes that can alter test execution. The scenario
requires a full-suite fallback and remains skipped. Whether PHPUnit should track a
selected set of runtime settings, or Infection should supply an additional recording
fingerprint, needs discussion.

A separate attempt to pass `-d precision=15` through `--test-framework-options`
revealed an existing argument-handling issue: Infection forwards `--d`, which PHPUnit
rejects as ambiguous. That issue is separate from TIA; no argument-parser fix is included here.

### Generated XML can track the wrong dependency lock

`04` places generated XML in a sibling directory outside the scenario project before
recording. Changing the scenario's `composer.lock` then leaves the old recording usable:
only CalculatorTest runs. PHPUnit's `Assumptions::composerLockFileNearest()` searches
upwards from the generated XML, so it discovers the fixture root's lock instead of
that scenario project's lock. The equivalent scenario with XML inside the project passes.
This confirms the integration TODO: generated XML needs a way to retain the project's
lock-file identity, independently of where Infection stores temporary files.

### Explicit impact queries miss changed external fixtures

`04` adds `#[UsesFixture('input.json')]` to UnrelatedTest, records a run that reads
that JSON file, and changes the fixture without changing the test ID or PHP file.
Infection's explicit query for Calculator still selects only CalculatorTest. The
expected initial selection includes UnrelatedTest as well. This is the same explicit-query
limitation as changed test bodies and providers, now reproduced for declared non-PHP
inputs. Such dependencies must be included in any proposed changed-input safeguard.

### Cache write failures do not disable TIA

Two deterministic path obstructions reproduce the missing fallback without relying on
Unix permissions (which privileged processes may bypass):

- Replacing the cache directory with a regular file makes PHPUnit reject the impact
  query: `Cannot run only the tests that are affected by what changed because no cache
  directory is configured`. Infection exits unsuccessfully.
- Replacing `test-impact-data` with a directory makes the initial run execute both tests,
  then emit an `fopen(...): Is a directory` warning. In this run the initial process
  ended with exit code 143 and Infection failed before mutation testing.

The skipped scenarios require Infection to disable automatic impact selection and
recording and complete mutation testing. Cache validation and diagnostics are Infection
integration work; handling a failed recording destination is also useful PHPUnit feedback.
These cases do not claim to cover every permission or filesystem failure.

### XML opt-outs are overridden

`05` warms a recording, then sets either `recordTestImpactData="false"` or
`recordTestRunHistory="false"` in project XML. The generated configuration and recorded
effective settings still enable both. `InitialConfigBuilder::build()` sets these
attributes unconditionally. Both scenarios are skipped. The corresponding CLI opt-outs
pass, so this is an Infection precedence issue rather than a PHPUnit opt-out failure.

An Infection-level TIA opt-out remains a separate API TODO: `phpUnit` currently has no
such schema option. The previous draft's speculative setting is documented here instead
of adding a scenario against an invented option name.

### Open question: explicit test selection

Should TIA further narrow an explicit `--filter`, `--group`, or `--testsuite` selection,
or should an explicit selector suppress automatic TIA? Neither policy may widen the
user's selection. Current implementation suppresses automatic TIA, and feature `05`
characterizes that behaviour; it does not close this question. If intersection is chosen,
add a scenario whose explicit selection contains both impacted and unrelated tests so
that the additional narrowing is observable.

### Empty intersections need an outcome policy

An explicit `--impacted-by=src/Calculator.php --filter=UnrelatedTest` selects no tests
from an otherwise valid recording. PHPUnit reports `No tests executed!` and exits 1;
Infection presents its general initial-test-failure diagnostic and exits 1. This is a
legitimate empty intersection, not missing impact data. The `@skip @decision_pending`
scenario expresses a possible successful-empty outcome with no generated or evaluated
mutants. Its final exit status, explanation, and report contract still need agreement.

Run all documented reproductions with:

```sh
cd tests/e2e/PHPUnit_TIA
php vendor/bin/behat --xdebug --profile=blocked
```

A feature path may be appended to focus the run. Failures are expected; the default
profile excludes them. Generated scenario files and numbered output logs remain under
`var/behat/scenarios/`; external-XML artefacts remain under `var/behat/external/`.

## Gotchas

- security issues
- can TIA be enabled?
  - If the impact cache cannot be created or written, disable TIA with a diagnostic and continue the initial run without it. Cache writability checks and fallback remain a TODO in the implementation.
  - Respect explicit user opt-outs and do not add impact selection when its prerequisites are disabled. Add a setting under Infection's `phpUnit` configuration to disable TIA entirely.
- communicate that a subset of tests was executed or why it wasn't
- Distinguish a legitimate empty test selection from missing or unusable impact data. PHPUnit already selects the full suite when the recording is missing, empty, or incompatible, or a queried path is unknown. A legitimate empty selection can still occur, for example when intersecting TIA with a user-selected group; Infection must handle and explain that outcome.
- cache / artefact re-use
- A cold impact-history cache provides no test-selection benefit on the first run. PHPUnit runs the full suite and records dependencies for subsequent runs.
- Concurrent Infection runs in the same project share the impact-history cache and may update it concurrently. Known gotcha; we do not intend to address it in this integration.
- Preserve PHPUnit's dependency-change safeguards when generating the initial configuration. PHPUnit already invalidates impact recordings when configuration, bootstrap scripts, or `composer.lock` change. Ensure it still discovers the project's `composer.lock` when Infection writes its generated XML outside the project.
- Ensure changes to declared external fixtures remain accounted for when Infection supplies explicit impact queries. Executed PHP coverage alone cannot reveal dependencies on files such as JSON fixtures or templates.
- coverage vs covers
- Whether automatic TIA should further narrow an explicit test selection remains an open question; see [selection boundaries](#open-question-explicit-test-selection).
- Select initial tests for all files being mutated, regardless of whether those files changed since the previous run. For example, running Infection against unchanged `B.php` still requires its coverage and tests; PHPUnit's `--only-impacted` could select no tests.
- Explicit impact queries must account for changed tests and data providers. In the PHPUnit snapshot being evaluated, `--impacted-by-file` queries recorded dependencies instead of comparing their current hashes. For example, a previously passing test covers only `A.php`; the user edits it to also cover `B.php`, then runs Infection against `B.php`. The old impact map can omit that test, leaving Infection with incomplete coverage. Partial updates cannot refresh its dependencies unless the test runs.
- Do not use TIA during mutant execution: we already know which tests to execute and in what order. Ensure that any pre-existing TIA configuration or configuration added for the initial run has no side effects in mutant processes. In particular, running a mutant must not update or invalidate the impact data recorded during the initial run.
- ???

## Feedback for PHPUnit


### Verbose output

The output can be quite verbose:

<details>
<summary>Output example</summary>

```shell
'/Users/tfidry/Project/Humbug/infection/vendor/bin/phpunit' '--configuration' '/var/folders/p3/lkw0cgjj2fq0656q_9rd0mk80000gn/T/infection/phpunitConfiguration.initial.infection.xml' '--exclude-source-from-xml-coverage' '--coverage-xml=/var/folders/p3/lkw0cgjj2fq0656q_9rd0mk80000gn/T/infection/coverage-xml' '--log-junit=/var/folders/p3/lkw0cgjj2fq0656q_9rd0mk80000gn/T/infection/junit.xml' '--impacted-by-file' '/var/folders/p3/lkw0cgjj2fq0656q_9rd0mk80000gn/T/infection/phpunit-impact-sources.txt' --explain
PHPUnit 13.4-dev by Sebastian Bergmann and contributors.

Recorded at 2026-09-30 09:23:53 UTC from what the tests executed and, for a test that ran in a process of its own, what that process loaded.

72 of 518 tests can be affected by what changed.

68 tests depend on something that changed:
 - Infection\Tests\Process\Runner\MutationTestingRunnerTest::test_it_does_not_create_processes_when_code_is_ignored_by_regex
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffSourceCodeMatcher.php
 - Infection\Tests\Differ\ChangedLinesRangeTest::test_it_can_be_created_for_a_line
   /Users/tfidry/Project/Humbug/infection/src/Differ/ChangedLinesRange.php
 - Infection\Tests\Differ\ChangedLinesRangeTest::test_it_can_be_created_for_a_range
   /Users/tfidry/Project/Humbug/infection/src/Differ/ChangedLinesRange.php
 - Infection\Tests\Differ\ChangedLinesRangeTest::test_it_can_be_created_with_an_end_line_lesser_than_a_start_line
   /Users/tfidry/Project/Humbug/infection/src/Differ/ChangedLinesRange.php
 - Infection\Tests\Differ\ChangedLinesRangeTest::test_it_can_check_if_it_contains_the_given_range#the mutation touches some of the changed lines
   /Users/tfidry/Project/Humbug/infection/src/Differ/ChangedLinesRange.php
 - Infection\Tests\Differ\ChangedLinesRangeTest::test_it_can_check_if_it_contains_the_given_range#the mutation touches all the changed lines
   /Users/tfidry/Project/Humbug/infection/src/Differ/ChangedLinesRange.php
 - Infection\Tests\Differ\ChangedLinesRangeTest::test_it_can_check_if_it_contains_the_given_range#the mutation touches all changed lines and more
   /Users/tfidry/Project/Humbug/infection/src/Differ/ChangedLinesRange.php
 - Infection\Tests\Differ\ChangedLinesRangeTest::test_it_can_check_if_it_contains_the_given_range#the first line of the mutation touches the changed lines
   /Users/tfidry/Project/Humbug/infection/src/Differ/ChangedLinesRange.php
 - Infection\Tests\Differ\ChangedLinesRangeTest::test_it_can_check_if_it_contains_the_given_range#the last line of the mutation touches the changed lines
   /Users/tfidry/Project/Humbug/infection/src/Differ/ChangedLinesRange.php
 - Infection\Tests\Differ\ChangedLinesRangeTest::test_it_can_check_if_it_contains_the_given_range#the mutation touches the changed lines
   /Users/tfidry/Project/Humbug/infection/src/Differ/ChangedLinesRange.php
 - Infection\Tests\Differ\ChangedLinesRangeTest::test_it_can_check_if_it_contains_the_given_range#the mutation touches some of the changed lines (before)
   /Users/tfidry/Project/Humbug/infection/src/Differ/ChangedLinesRange.php
 - Infection\Tests\Differ\ChangedLinesRangeTest::test_it_can_check_if_it_contains_the_given_range#the mutation touches some of the changed lines (after)
   /Users/tfidry/Project/Humbug/infection/src/Differ/ChangedLinesRange.php
 - Infection\Tests\Differ\ChangedLinesRangeTest::test_it_can_check_if_it_contains_the_given_range#the mutation does not affect any changed lines (before)
   /Users/tfidry/Project/Humbug/infection/src/Differ/ChangedLinesRange.php
 - Infection\Tests\Differ\ChangedLinesRangeTest::test_it_can_check_if_it_contains_the_given_range#the mutation does not affect any changed lines (after)
   /Users/tfidry/Project/Humbug/infection/src/Differ/ChangedLinesRange.php
 - Infection\Tests\Differ\ChangedLinesRangeTest::test_it_can_check_if_it_contains_the_given_range#invalid range given (start & end inversed) still contained
   /Users/tfidry/Project/Humbug/infection/src/Differ/ChangedLinesRange.php
 - Infection\Tests\Differ\DiffColorizerTest::test_id_adds_colours_to_a_given_diff#full-deletion
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffColorizer.php
 - Infection\Tests\Differ\DiffColorizerTest::test_id_adds_colours_to_a_given_diff#full-addition
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffColorizer.php
 - Infection\Tests\Differ\DiffColorizerTest::test_id_adds_colours_to_a_given_diff#partial-deletion
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffColorizer.php
 - Infection\Tests\Differ\DiffColorizerTest::test_id_adds_colours_to_a_given_diff#partial-addition
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffColorizer.php
 - Infection\Tests\Differ\DiffColorizerTest::test_id_adds_colours_to_a_given_diff#deletion-and-addition
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffColorizer.php
 - Infection\Tests\Differ\DiffColorizerTest::test_id_adds_colours_to_a_given_diff#bug-1999
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffColorizer.php
 - Infection\Tests\Differ\DiffColorizerTest::test_id_adds_colours_to_a_given_diff#multiple-removed-lines
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffColorizer.php
 - Infection\Tests\Differ\DiffColorizerTest::test_id_adds_colours_to_a_given_diff#code string containing symfony style-tags
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffColorizer.php
 - Infection\Tests\Differ\DiffColorizerTest::test_id_adds_colours_to_a_given_diff#surrounding comment containing symfony style-tags
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffColorizer.php
 - Infection\Tests\Differ\DiffColorizerTest::test_id_adds_colours_to_a_given_diff#multibyte characters full-deletion
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffColorizer.php
 - Infection\Tests\Differ\DiffColorizerTest::test_id_adds_colours_to_a_given_diff#multibyte characters partial-deletion
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffColorizer.php
 - Infection\Tests\Differ\DiffSourceCodeMatcherTest::test_it_matches_diff_with_provided_regex#Method name with PublicVisibility mutator
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffSourceCodeMatcher.php
 - Infection\Tests\Differ\DiffSourceCodeMatcherTest::test_it_matches_diff_with_provided_regex#Method name with MethodCallRemoval mutator
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffSourceCodeMatcher.php
 - Infection\Tests\Differ\DiffSourceCodeMatcherTest::test_it_matches_diff_with_provided_regex#Method name with not related PublicVisibility mutator
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffSourceCodeMatcher.php
 - Infection\Tests\Differ\DiffSourceCodeMatcherTest::test_it_matches_diff_with_provided_regex#Method call on object with MethodCallRemoval mutator
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffSourceCodeMatcher.php
 - Infection\Tests\Differ\DiffSourceCodeMatcherTest::test_it_matches_diff_with_provided_regex#All methods of static class calls with MethodCallRemoval mutator
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffSourceCodeMatcher.php
 - Infection\Tests\Differ\DiffSourceCodeMatcherTest::test_it_matches_diff_with_provided_regex#Method name with the minus operator
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffSourceCodeMatcher.php
 - Infection\Tests\Differ\DiffSourceCodeMatcherTest::test_it_matches_diff_with_provided_regex#Regex containing common delimiters should not lead to syntax error
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffSourceCodeMatcher.php
 - Infection\Tests\Differ\DiffSourceCodeMatcherTest::test_it_matches_diff_with_provided_regex#Regex containing less common delimiters should not lead to syntax error
   /Users/tfidry/Project/Humbug/infection/src/Differ/DiffSourceCodeMatcher.php
 - Infection\Tests\Differ\DifferTest::test_it_shows_the_diff_between_two_sources_but_limiting_the_displayed_lines#empty
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\DifferTest::test_it_shows_the_diff_between_two_sources_but_limiting_the_displayed_lines#nominal
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\DifferTest::test_it_shows_the_diff_between_two_sources_but_limiting_the_displayed_lines#no change
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\DifferTest::test_it_shows_the_diff_between_two_sources_but_limiting_the_displayed_lines#line excess
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\DifferTest::test_it_shows_the_diff_between_two_sources_but_limiting_the_displayed_lines#line excess with multiple changes
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\DifferTest::test_it_shows_the_diff_between_two_sources_but_limiting_the_displayed_lines#a line with the carriage return as the only difference
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\DifferTest::test_it_shows_the_diff_between_two_sources_but_limiting_the_displayed_lines#a line with change and the carriage return as the only difference
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\DifferTest::test_it_can_diff_the_code_as_arrays#empty
   /Users/tfidry/Project/Humbug/infection/src/Differ/Tokens.php
 - Infection\Tests\Differ\DifferTest::test_it_can_diff_the_code_as_arrays#nominal
   /Users/tfidry/Project/Humbug/infection/src/Differ/Tokens.php
 - Infection\Tests\Differ\DifferTest::test_it_can_diff_the_code_as_arrays#no change
   /Users/tfidry/Project/Humbug/infection/src/Differ/Tokens.php
 - Infection\Tests\Differ\DifferTest::test_it_can_diff_the_code_as_arrays#line excess
   /Users/tfidry/Project/Humbug/infection/src/Differ/Tokens.php
 - Infection\Tests\Differ\DifferTest::test_it_can_diff_the_code_as_arrays#line excess with multiple changes
   /Users/tfidry/Project/Humbug/infection/src/Differ/Tokens.php
 - Infection\Tests\Differ\DifferTest::test_it_can_diff_the_code_as_arrays#a line with the carriage return as the only difference
   /Users/tfidry/Project/Humbug/infection/src/Differ/Tokens.php
 - Infection\Tests\Differ\DifferTest::test_it_can_diff_the_code_as_arrays#a line with change and the carriage return as the only difference
   /Users/tfidry/Project/Humbug/infection/src/Differ/Tokens.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#empty diff
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#basic diff
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#trailing line break is added when the diff does not have one
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#no line end warning tokens are preserved as blank lines
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#no line end warnings are ignored when there is no change
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#carriage return terminated line ending warnings are preserved
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#line ending warnings without line breaks are terminated
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#missing line break warnings are added after the trailing unchanged line
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#carriage return terminated trailing unchanged lines get missing line break warnings
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#trailing unchanged lines control their own missing line break warning
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#missing line break warnings are added after the latest added line
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#missing line breaks are added for changed lines at the end of a file
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#missing line breaks are added for added lines before removed lines
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#only the latest added and removed lines get missing line break warnings
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#only the latest added and removed lines are checked for missing line breaks
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_builds_a_unified_diff#distant changes are split into separate hunks
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_rejects_invalid_diff_entries#entry is not an array
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_rejects_invalid_diff_entries#entry has more than two elements
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_rejects_invalid_diff_entries#token is not a string
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php
 - Infection\Tests\Differ\UnifiedDiffOutputBuilderTest::test_it_rejects_invalid_diff_entries#diff type is unknown
   /Users/tfidry/Project/Humbug/infection/src/Differ/UnifiedDiffOutputBuilder.php

4 tests have never been recorded:
 - Infection\Tests\TestFramework\Coverage\CoverageChecker\CoverageCheckerTest::test_it_needs_code_coverage_generator_enabled_if_coverage_is_not_provided
 - Infection\Tests\TestFramework\Coverage\JUnit\JUnitReportLocatorTest::test_it_cannot_locate_the_default_report_with_the_wrong_case_on_a_case_sensitive_system
 - Infection\Tests\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocatorTest::test_it_cannot_locate_the_default_report_with_the_wrong_case_on_a_case_sensitive_system
 - Infection\Tests\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocatorTest::test_it_cannot_find_the_report_if_there_is_more_than_one_valid_report

```

</details>

In this situation, listing the test cases would be sufficient, and the user could expand with an increased verbosity.
