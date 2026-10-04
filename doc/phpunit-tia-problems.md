# PHPUnit TIA: integration problems and open questions

Checked against [PHPUnit PR #6919 at `e21b72d4`](https://github.com/sebastianbergmann/phpunit/tree/e21b72d4ac3a9d9351eaa065638dd5e6259879bd)
(`13.5-dev`) and Infection's current proof of concept. The feature is not merged.
Both the root and Behat fixture Composer projects pin this commit.
This is the consolidated record of integration findings, decisions, and remaining questions.
See [the option overview](phpunit-tia-options.md) for the API.

Rechecked on 2026-10-04 with PHP 8.4.22 and Xdebug 3.5.3:

| Behat profile | Result |
| --- | --- |
| Default | 22 passed, 3 failed: both shared-recording cases and the unknown-source explanation. |
| `blocked` | All 11 examples failed. These are the `@skip` cases below, including one proposed outcome. |

## What Infection needs

Infection needs initial line-to-test coverage for every source file selected for mutation,
including unchanged files. It then runs each mutant against its covering tests.
TIA should reduce the initial run without losing coverage or covering tests. Mutant runs
must neither use TIA selection nor change its recordings.

The proof of concept records observed execution by default and passes selected source files
through `--impacted-by-file`. Declared-target recording is optional; it does not replace
the execution coverage Infection needs.

### Integration direction and selection policy

Enable TIA whenever supported and safe, including projects with no prior TIA setup.
A cold run executes the full suite and prepares later runs; it saves no initial tests itself.
Keep XML coverage and JUnit for mutation generation, covering-test selection, and timings.
The proof of concept requires no replacement of Infection's coverage or mutation pipeline.

Select declared targets with `--test-framework-extra-args=--derive-test-impact-data-from-coverage-targets`
or `testFrameworkExtraArgs`; Behat currently uses the older `--test-framework-options` option.
The intended strategy selector is the extra arguments, not project XML or a new Infection
strategy setting. Choosing declared targets avoids observed dependency recording, but does
not remove fresh coverage collection when Infection needs it. Impact options apply only to
the initial run. The proposed replacement of `mapSourceClassToTestStrategy` still needs migration work.

| Selection | Current initial-run policy | Behat evidence |
| --- | --- | --- |
| Restricted source only | Query all resolved mutation files. Git-diff and legacy source filters feed the same list; line restrictions become containing-file queries. | Positional sources in [01][f01]–[05][f05]; no Git/line-filter scenario. |
| Explicit tests, with or without source restrictions | Honour requested tests and suppress automatic TIA. For example, `infection src/Calculator.php tests/CalculatorTest.php` limits mutations and tests independently. | Filter/group/suite examples in [05][selectors]; no positional-test-path scenario. |
| No restriction | Run the configured initial suite and record dependencies. | No unrestricted-source Behat scenario. |
| Explicit impact query | Leave the user's query in control. Separate option-value tokens also conservatively suppress automatic selection; the integration does not duplicate PHPUnit's full argument grammar. | Empty-intersection case in [05][empty]; no general option-grammar scenario. |

Explicit test precedence was previously described as agreed and an ADR candidate, but later
notes reopened intersection. No ADR was written. Neither policy may widen the requested
tests. Explicitly requested test paths are not interchangeable with changed-test paths added
for recording freshness.

### Recording prerequisites and coverage enforcement

| Requirement | Observed execution | Declared targets |
| --- | --- | --- |
| PHPUnit supporting TIA | Required; current gate matches only `13.5-dev`, which alone does not identify the pinned commit. | Same. |
| Persistent, writable cache | Required to retain new recordings. | Same. |
| Enabled test-run history | Required for selection. | Same. |
| Non-empty source/coverage filter | Required for recording. | Required to resolve targets. |
| Coverage driver and collection | Required for recording, not selection from existing data. | Not needed to derive dependencies; needed if Infection generates fresh coverage. |
| Mandatory coverage metadata | Not required. | Required for unsized tests and every effective size-specific requirement. |
| Strict coverage checking | Not required for recording. | Recommended: its absence warns but does not disable derivation. Warning-failure policy can still fail the run. |

No existing recording, Git repository, or `composer.lock` is required. Missing prerequisites
must be distinguished from missing data. Infection must not impose metadata requirements
to enable an optimisation.

| PHPUnit setting (default `false`) | Meaning |
| --- | --- |
| `requireCoverageMetadata` | Require a coverage target or explicit `CoversNothing`; `Uses*` alone is insufficient. No coverage driver needed for this check. |
| `requireCoverageContribution` | Require an executed line after coverage filtering; it need not be unique to the test. Coverage opt-outs are exempt. Not a TIA prerequisite. |
| `beStrictAboutCoverageMetadata` | Check execution outside declared covered/used code during coverage collection. This validates declarations, not whether a test contributes coverage. |

These checks do not select a recording strategy. Violations can make tests risky;
`failOnRisky` controls the exit outcome separately. For derivation, `requireCoverageMetadata="true"`
must not be overridden to false by any of `requireCoverageMetadataOnSmallTests`,
`requireCoverageMetadataOnMediumTests`, or `requireCoverageMetadataOnLargeTests`.
Behat [05][f05] covers the global metadata gate and unexecuted declared code, not the full
size-specific, strictness, contribution, or missing-driver matrix.
When mandatory metadata is missing, Infection logs a notice and runs without automatic
TIA; PHPUnit itself warns and disables derivation when its metadata gate is not satisfied.

## 1. Explicit queries can omit newly relevant tests

PHPUnit treats explicit paths as the change set. Infection supplies mutation targets.
Those are different sets.

1. Record test A covering source A and test B covering source B.
2. Edit test B to cover A as well, preserving its test ID.
3. Run Infection on A. The recorded dependencies select test A only.

The initial coverage can omit lines or covering tests. Equal MSI does not establish safety:
another test may already kill the affected mutant.

[`Selector::explain()`][selector] still chooses between explicit dependency lookup and
automatic hash comparison. Adding `--only-impacted` to an explicit query does not combine
them. Automatic detection alone is insufficient because unchanged mutation targets still
need coverage.

Needed: dependencies on selected source **plus** safeguards for changed tests and inputs,
or a conservative full-run fallback. A current Git diff is not necessarily the change set
since the recording. This is an integration requirement, not evidence that PHPUnit violates
its explicit-query contract.

| Behat reproduction | What it establishes |
| --- | --- |
| [02: An existing test starts covering another source file][changed-test] | Existing test omitted after its body changes. `@skip`. |
| [02: Changed data-provider inputs establish a previously unknown dependency][changed-provider] | Same omission with an unchanged data-set ID. `@skip`. |
| [04: A changed external fixture remains relevant to an explicit impact query][changed-fixture] | A test declaring `UsesFixture` is omitted after its JSON input changes. `@skip`. The edit adds an unused field; this checks selection, not lost coverage or changed results. |

The first two scenarios contain comparisons against a full run, but currently fail at the
test-selection assertion before reaching those comparisons. They exercise observed recording;
they do not establish safety for every declared-target case.

## 2. Configuration and recording identity

| Problem | Current mechanism and consequence | Behat coverage |
| --- | --- | --- |
| Sharing a recording fails even with the same cache | PHPUnit resolves recording paths and hashes the source definition relative to the XML directory. The same source is `src` from project XML and `../../../../src` from generated XML. Both sharing directions fall back with `what is first-party code changed since the test impact data was recorded`. | [04: both same-cache sharing scenarios][sharing], active. Supersedes the old claim that sharing works. |
| Generated XML outside the project tracks the wrong lock | Lock discovery walks upwards from the XML directory. In the reproduction it finds the fixture root's lock, so changing the scenario project's lock does not invalidate its recording. | [04: Generated XML outside the project still tracks the project's dependency lock][external-xml], `@skip`. The inside-project lock-change example passes. |
| Interpreter settings are not part of the recorded PHPUnit configuration | `--initial-tests-php-options="-d precision=15"` changes PHP's runtime setting without changing PHPUnit's merged configuration. Selection remains narrow. | [04: A changed PHP runtime setting invalidates a warm recording][runtime], `@skip`. Proves missing invalidation for this setting, not an incorrect result. |

The first two follow from [`Application::baseDirectoryOf()`][application] and
[`Assumptions`][assumptions]. Rewriting source paths to absolute paths does not preserve
their identity after PHPUnit makes them relative to another XML directory.
The sharing failure costs reuse; the lock and runtime cases miss invalidation.
The runtime fingerprint is built from [`ExecutionSettings`][settings], including configured
`<php>` values; it is not a fingerprint of every effective INI or environment value.

Questions for PHPUnit: can a caller preserve the original project's recording base and
lock-file identity while using generated XML? Should additional runtime inputs be selected
by PHPUnit or supplied by the caller?

### What invalidates data

PHPUnit checks recording format, PHP/PHPUnit versions, strategy, and execution assumptions
before selection. Missing, unreadable, or malformed data also cannot be reused. Automatic
selection then compares dependency contents (`xxh128`), not timestamps or Git state.
Changed, missing, or unreadable dependencies select affected tests; unexplained source or
watched-file changes can select everything. Explicit paths replace that second comparison,
but retain the recording-compatibility checks. Repeated explicit paths and list-file paths
form a union; they can name source, tests, fixtures, or directories.

History stores defects and timings separately from impact dependencies; the impact-settings
hash does not validate the history file. Previously unsuccessful, unknown, and unrecordable
tests remain selected. Tests not executed cannot refresh their observed dependencies.

Execution assumptions include configured PHP values, suite bootstraps, extensions, process
isolation, and global/static backup settings. Reporting, output, failure policy, and order
are excluded. Arbitrary database state and ambient environment values are not fingerprinted.
Behat [04][f04] covers selected invalidation cases, not this entire matrix.

### What Infection changes in XML

[`InitialConfigBuilder`][initial-builder] writes `phpunitConfiguration.initial.infection.xml`;
the project file remains intact. These transformations were checked in code. Behat exercises
their combined effect, not each transformation independently.

| Setting | Transformation |
| --- | --- |
| Formatting and paths | Reserialize XML; absolutize the root bootstrap, matched suite exclusions, and `directory`/`file` nodes. |
| Source includes | On PHPUnit >=12, preserve existing includes or supply Infection's configured directories. Older versions can narrow them to mutation targets. |
| Bootstrap | Preserve project bootstrap contents. Only mutant runs use Infection's generated interceptor bootstrap. |
| Order | Default to random when unset, with dependency resolution if also unset; supported older versions use defects plus random. |
| Failure policy | Set `stopOnDefect` on PHPUnit >=10, otherwise `stopOnFailure`; default missing `failOnRisky` and `failOnWarning` to true where supported. |
| Output | Disable colours/stderr; remove printer and configured logging/coverage reports. Supply fresh XML coverage and JUnit destinations through CLI arguments. |
| TIA with fresh coverage | Enable recording/history, default derivation off, and use `.infection/phpunit`, outside the cleaned temporary directory. The impact query is a CLI argument. |
| Other runs | Disable result caching/history; remove history-dependent order on PHPUnit >=13.3. |

Mutant XML removes impact-recording attributes and mutant commands strip recording and
selection options, including valueless switches. [03][f03] checks both strategies, actual
loaded/executed mutant tests, unchanged shared impact/history hashes, and subsequent warm reuse.

## 3. Infection integration gaps

These are distinct from PHPUnit's selection contract.

| Gap | Current behaviour | Behat coverage |
| --- | --- | --- |
| Project cache ignored | Infection replaces `cacheDirectory` with `.infection/phpunit`, missing recordings in the project's configured cache. Fixing this alone does not fix the shared-recording failure above. | [01: Infection reuses impact data from a previous PHPUnit run][project-cache], `@skip`. |
| XML opt-outs overridden | Initial XML forces impact recording and history on. Explicit CLI opt-outs work. | [05: XML opt-out outline][xml-opt-out], two `@skip` examples; CLI counterparts pass. |
| Cache failure aborts the run | A file at the cache-directory path or directory at the recording-file path prevents the intended fallback without TIA. | [04: Cache obstruction outline][cache-obstruction], two `@skip` examples. Does not cover every filesystem failure. |
| Supplied coverage has no completed TIA policy | With `--coverage`, Infection omits report arguments but does not automatically add `--no-coverage`. It skips automatic TIA setup and disables history in generated XML, while project recording settings can remain enabled. | **No Behat scenario.** Verified in [`InitialConfigBuilder`][initial-builder] and [`PhpUnitAdapter`][adapter]. |

### Supplied-coverage proposal

`--coverage` supplies reports but still runs initial tests. `$skipCoverage` means reports
were supplied; `$collectCoverage` is its inverse, not the TIA strategy. Supplied reports still
undergo Infection's coverage validation and are not themselves a PHPUnit impact recording.

The recorded decision was to add `--no-coverage` automatically for supplied reports. Merely
removing report destinations does not stop TIA or extensions requesting coverage. The
proposed selection policy remains unimplemented and has **no Behat scenario**:

| Strategy | Proposed behaviour with supplied coverage |
| --- | --- |
| Observed execution | Select from compatible data without refreshing observed dependencies; fall back to the eligible full suite when unusable. |
| Declared targets | Continue recording and selection subject to metadata prerequisites, without coverage collection. |
| Disabled TIA | Run ordinary initial tests without collecting coverage or silently switching strategy. Honour explicit opt-outs. |

`--no-coverage` disables observed recording but permits derivation; `--do-not-record-test-impact-data`
disables both and is not an equivalent override. Selection still needs history, cache,
compatible assumptions, and changed-input safeguards. `--skip-initial-tests` additionally
requires supplied reports and removes the run TIA would optimise.

## Open decisions and missing evidence

| Topic | Current evidence / question | Behat coverage |
| --- | --- | --- |
| Explicit test restrictions | Infection suppresses automatic TIA for `--filter`, `--group`, and `--testsuite`. Older findings call this agreed; session notes call it open. Confirm whether to preserve that policy or intersect selections. | [05: explicit-selector outline][selectors], three passing `@current_behavior` examples. No example distinguishes the policies using a selection containing both impacted and unrelated tests; no positional-test-path scenario. |
| Empty intersection | An explicit impact query plus an unrelated filter produces no tests; Infection reports an initial-test failure. Exit status, diagnostic, and empty reports need a decision. | [05: An empty explicit impact intersection is distinguished from missing data][empty], `@skip @decision_pending`. Its successful-empty expectation is a proposal. |
| Infection-level opt-out | No dedicated TIA setting exists under `phpUnit`; CLI recording/history opt-outs are available. | No dedicated-setting scenario. |
| Configuration introspection | Impact-selection options are absent from the merged configuration passed to extensions. The recording extension reparses argv through PHPUnit's CLI `Builder`. Could PHPUnit expose the effective mode and paths there? This does not require XML equivalents. | No dedicated scenario; [the extension][extension] demonstrates the workaround. |
| Explanation verbosity | `--explain-impacted` still lists individual tests and dependency paths. The request for class-level output with optional detail is a usability preference. Ordinary run output is a separate summary. | No scenario. Verified in [`ExplainImpactedCommand`][explain]. |
| Performance and concurrency | Benchmark complete cold/warm Infection runs on representative projects. Concurrent runs share the cache; concurrency work was explicitly deferred from this integration. PHPUnit already locks impact-data read/merge/write, so shared storage alone does not prove a write bug. | No benchmark or concurrency scenario. See [`TestImpactDataFile`][impact-file]. |
| Release and migration | The version gate is experimental. Migration from `mapSourceClassToTestStrategy` remains unspecified. | No release/migration scenario. |
| Selection diagnostics | Explain that a subset ran or why fallback occurred, and expose the recording time. PHPUnit has summary/reason/timestamp output; Infection's final presentation remains to be settled. | [04][f04] asserts selected fallback reasons; no timestamp or complete console-UX scenario. |
| Security | Earlier notes named “security issues” without a threat, finding, or reproduction. Retain as an unspecified review question, not a confirmed vulnerability. | No scenario. |

The Behat suite exercises positional source selection. Git-diff and line restrictions,
supplied coverage, and configuration overrides are not covered by these scenarios.

### Separate command-line follow-ups

| Item | Status and follow-up | Behat coverage |
| --- | --- | --- |
| `--no-coverage` without supplied reports | Currently rejected before the TIA version gate. Move validation to general extra-argument handling and file the pre-existing bug separately; this option predates TIA ([PHPUnit 5.7.27][old-no-coverage]). | None. |
| Configuration overrides | `--configuration`, `-c`, and `--no-configuration` are temporarily rejected on the TIA path because they bypass generated XML. Fix configuration selection separately; the old duplicate-configuration workaround is no longer needed. | None. |
| Legacy `-d` parsing | The old manual probe through `--test-framework-options` forwarded `-d` as `--d`, rejected by PHPUnit as ambiguous. The legacy normalization still prefixes option names with `--` in `ConfigurationFactory`; separate parser issue. The runtime scenario uses `--initial-tests-php-options` instead. | None; checked in code, not re-executed. |
| `initial-test:run` source filtering | The command now uses shared source-filter handling, including no filter by default. Extract this fix and its regression test from the TIA change. The old unconditional Git filter could raise `NoSourceFound` for untracked sources; it dates to [PR #2762][initial-command-pr] (`1fdaed86b`), whose stated contract was the regular command's options. | No TIA Behat scenario. The [command test][initial-command-test] asserts that default execution never asks Git for a base reference. |

## What is already established

Passing scenarios cover cold/warm reuse within Infection, new tests and source files,
source edits, strengthened assertions, and retention across partial runs ([01][f01], [02][f02]).
Both recording strategies preserve mutant isolation and subsequent warm reuse ([03][f03]);
the fixture has one covering test per mutant, so it does not prove ordering among several tests.
Automatic activation, CLI opt-outs, metadata prerequisites, and the distinction between
declared targets and executed coverage are covered in [05][f05].

Missing, empty, and version-incompatible recordings fall back and become reusable ([04][f04]).
Execution-setting, bootstrap, and lock-file edits invalidate recordings when generated XML
stays within the project.
The unrecorded-source scenario also runs all tests, but fails on its expected explanation:
configuration identity invalidates the recording before the unknown-path check. It therefore
does not currently isolate unknown-path fallback.

Whole-XML hashing is obsolete: [the upstream response][upstream-response] replaced it with
selected execution assumptions and invalidation reasons. This removes
report-formatting differences as a cause, but does **not** establish interchangeability of
recordings across configuration locations.

The older `09b54d872` and `5f4f80f15`/`13.4-dev` observations are superseded by the pinned
`13.5-dev` results above. In particular, earlier claims of shared-cache reuse and safe XML
relocation do not hold generally now. The old assertion that changed tests had no scenario
is obsolete too. Earlier manual controls reported additional covering tests with unchanged
MSI; the current blocked scenarios stop before their full-run controls.

### Evidence and limits

The intended matrix includes projects with and without TIA, a single run (such as CI),
stronger tests after an escaped mutant, and added source/tests. [01][f01]–[05][f05] cover these
categories, but do not form a complete cross-product or a CI-environment test.

`logs.execution` captures initial identities, line-to-test coverage, generated/evaluated
mutations, commands, XML, and cache snapshots; see [the report contract](execution-report.md).
The PHPUnit extension adds actual loaded/executed identities and effective configuration.
Passing development-cycle controls compare coverage, mutation hashes/statuses, and MSI in
the same project directory, using snapshots and `--do-not-record-test-impact-data` to disable
recording and automatic selection. This avoids path/hash normalization; timings are excluded.

A cache file or enabled setting does not prove warm reuse. A killed mutant does not identify
its killing test; that needs framework failure output. Cold/warm fixture runs select two/one
tests with preserved results. Cache-obstruction cases use path types, not Unix permissions:
the obstructed cache-directory path reports no configured cache; the recording-file obstruction emits
`fopen(...): Is a directory` and the observed initial process exits 143. Neither completes
the requested fallback without TIA.

Feedback to PHPUnit concerns explicit-query safeguards, configuration/lock identity,
runtime inputs, cache-write diagnostics, merged-configuration introspection, and concise
explanations. No upstream messages or bug reports were posted during these documentation audits.

## Reproduce

From `tests/e2e/PHPUnit_TIA`, with PHP 8.4.1+, a coverage driver, and installed dependencies:

```sh
php vendor/bin/behat --xdebug
php vendor/bin/behat --xdebug --profile=blocked
```

The default profile excludes `@skip`; the blocked profile executes those scenarios and
their desired assertions. A scenario that stops at an earlier failure does not verify its
later assertions. Generated projects and logs remain under `var/behat/scenarios/`.
External-XML artefacts remain under `var/behat/external/`. Append a feature path or `--name`
to focus either profile; setup details are in the [fixture README](../tests/e2e/PHPUnit_TIA/README.md).

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

[f01]: ../tests/e2e/PHPUnit_TIA/features/01-initial-run.feature
[f02]: ../tests/e2e/PHPUnit_TIA/features/02-development-cycle.feature
[f03]: ../tests/e2e/PHPUnit_TIA/features/03-mutant-isolation.feature
[f04]: ../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature
[f05]: ../tests/e2e/PHPUnit_TIA/features/05-configuration-and-boundaries.feature
[changed-test]: ../tests/e2e/PHPUnit_TIA/features/02-development-cycle.feature#L39
[changed-provider]: ../tests/e2e/PHPUnit_TIA/features/02-development-cycle.feature#L69
[changed-fixture]: ../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature#L129
[sharing]: ../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature#L104
[external-xml]: ../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature#L90
[runtime]: ../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature#L75
[project-cache]: ../tests/e2e/PHPUnit_TIA/features/01-initial-run.feature#L10
[xml-opt-out]: ../tests/e2e/PHPUnit_TIA/features/05-configuration-and-boundaries.feature#L47
[cache-obstruction]: ../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature#L158
[selectors]: ../tests/e2e/PHPUnit_TIA/features/05-configuration-and-boundaries.feature#L114
[empty]: ../tests/e2e/PHPUnit_TIA/features/05-configuration-and-boundaries.feature#L156
[initial-builder]: ../src/TestFramework/PhpUnit/Config/Builder/InitialConfigBuilder.php
[adapter]: ../src/TestFramework/PhpUnit/Adapter/PhpUnitAdapter.php
[extension]: ../tests/e2e/PHPUnit_TIA/phpunit/RecordExecutionExtension.php
[selector]: https://github.com/sebastianbergmann/phpunit/blob/e21b72d4ac3a9d9351eaa065638dd5e6259879bd/src/Runner/TestImpactAnalysis/Selector.php
[application]: https://github.com/sebastianbergmann/phpunit/blob/e21b72d4ac3a9d9351eaa065638dd5e6259879bd/src/TextUI/Application.php
[assumptions]: https://github.com/sebastianbergmann/phpunit/blob/e21b72d4ac3a9d9351eaa065638dd5e6259879bd/src/Runner/TestImpactAnalysis/Assumptions.php
[settings]: https://github.com/sebastianbergmann/phpunit/blob/e21b72d4ac3a9d9351eaa065638dd5e6259879bd/src/Runner/TestImpactAnalysis/ExecutionSettings.php
[explain]: https://github.com/sebastianbergmann/phpunit/blob/e21b72d4ac3a9d9351eaa065638dd5e6259879bd/src/TextUI/Command/Commands/ExplainImpactedCommand.php
[impact-file]: https://github.com/sebastianbergmann/phpunit/blob/e21b72d4ac3a9d9351eaa065638dd5e6259879bd/src/Runner/TestImpactAnalysis/TestImpactDataFile.php
[upstream-response]: https://github.com/sebastianbergmann/phpunit/pull/6919#issuecomment-5844689848
[old-no-coverage]: https://github.com/sebastianbergmann/phpunit/blob/5.7.27/src/TextUI/Command.php
[initial-command-pr]: https://github.com/infection/infection/pull/2762
[initial-command-test]: ../tests/phpunit/Command/InitialTest/InitialTestRunCommandTest.php
