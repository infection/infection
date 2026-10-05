# PHPUnit TIA integration

These findings were checked against [PHPUnit PR #6919 at `e21b72d4`](https://github.com/sebastianbergmann/phpunit/tree/e21b72d4ac3a9d9351eaa065638dd5e6259879bd)
(`13.5-dev`) and Infection's current proof of concept. The feature has not been merged.
Both the root and Behat fixture Composer projects pin this commit.
This directory describes the integration approach, verified behaviour and remaining work.
See [the option overview](../phpunit-tia-options.md) for the PHPUnit API.

The findings were rechecked on 2026-10-05 with PHP 8.4.22 and Xdebug 3.5.3 after updating
the fixture's Composer dependencies. PHPUnit remained at the pinned commit:

| Behat profile | Result |
| --- | --- |
| Default | All 22 examples passed. |
| `blocked` | All 14 examples failed when run explicitly, including one proposed outcome. |

Only scenarios with failing assertions are tagged `@skip` and excluded from the default
profile. These include both shared-recording scenarios and the unknown-source scenario,
which runs the full suite but reports a different explanation from the one expected.

## What Infection needs

Infection needs initial line-to-test coverage for every source file selected for mutation,
including unchanged files. It then runs each mutant against its covering tests.
TIA should reduce the initial run without losing coverage or covering tests. Mutant runs
must neither use TIA selection nor change its recordings.

The proof of concept records observed execution by default and passes selected source files
through `--impacted-by-file`. Declared-target recording is optional; it does not replace
the execution coverage Infection needs.

### Integration direction and selection policy

Enable TIA wherever supported and safe, including in projects without prior TIA setup.
A cold run executes the full suite and prepares later runs; it does not reduce its own
initial test run. Retain XML coverage and JUnit reports for mutation generation,
covering-test selection and timings. The proof of concept requires no replacement of
Infection's coverage or mutation pipeline.

Select declared targets with `--test-framework-extra-args=--derive-test-impact-data-from-coverage-targets`
or `testFrameworkExtraArgs`; Behat currently uses the older `--test-framework-options` option.
The recording strategy is intended to be selected through extra arguments, rather than
project XML or a new Infection setting. Choosing declared targets avoids observed dependency
recording, but Infection still collects fresh coverage when needed. Impact options apply
only to the initial run. Migration work is still needed for the proposed replacement of
`mapSourceClassToTestStrategy`.

| Selection | Current initial-run policy | Behat evidence |
| --- | --- | --- |
| Restricted source only | Query all resolved source files selected for mutation. Git-diff and legacy source filters supply the same list; line restrictions query the containing files. | Positional source paths in [01][f01]–[05][f05]; no Git-diff or line-filter scenario. |
| Explicit tests, with or without source restrictions | Honour requested tests and suppress automatic TIA. For example, `infection src/Calculator.php tests/CalculatorTest.php` limits mutations and tests independently. | Filter/group/suite examples in [05][selectors]; no positional-test-path scenario. |
| No restriction | Run the configured initial suite and record dependencies. | No unrestricted-source Behat scenario. |
| Explicit impact query | Leave the user's query in control. Separate option-value tokens also conservatively suppress automatic selection; the integration does not duplicate PHPUnit's full argument grammar. | Empty-intersection case in [05][empty]; no general option-grammar scenario. |

Whether TIA should narrow an [explicit test selection](infection/explicit-test-selection.md)
remains open. Neither policy may widen the requested tests. Explicitly requested test paths
are not interchangeable with changed-test paths added for recording freshness.

## Feedback for PHPUnit

Each document covers Infection's use case, current behaviour, request and evidence.
The selection request reflects an integration need; it does not claim that PHPUnit violates its
explicit-query contract. Usability and API requests have no dedicated failing scenario.

- [Changed tests can be missed by explicit source queries](phpunit/changed-tests.md).
- [Sharing recordings between PHPUnit and Infection](phpunit/recording-sharing.md).
- [Tracking the project's dependency lock with generated XML](phpunit/dependency-lock.md).
- [Invalidating recordings when PHP runtime settings change](phpunit/runtime-settings.md).
- [Reporting impact-cache write failures](phpunit/cache-write-errors.md).
- [Exposing effective selection options to extensions](phpunit/configuration-introspection.md).
- [Offering a concise impact explanation](phpunit/explanation-verbosity.md).

## Infection integration work

These documents distinguish reproduced gaps from proposed policies and untested follow-ups.

- [Reusing the project's configured cache](infection/project-cache.md).
- [Respecting explicit XML opt-outs](infection/xml-opt-outs.md).
- [Continuing without TIA when the cache is unusable](infection/cache-fallback.md).
- [Using supplied coverage reports](infection/supplied-coverage.md).
- [Handling explicit test selection](infection/explicit-test-selection.md).
- [Handling an empty impact selection](infection/empty-selection.md).
- [Deciding whether Infection needs its own opt-out](infection/tia-opt-out.md).
- [Explaining selection in Infection's output](infection/selection-diagnostics.md).
- [Measuring performance and assessing concurrent runs](infection/performance-and-concurrency.md).
- [Replacing the experimental gate and planning migration](infection/release-and-migration.md).

The following command-line fixes should be handled separately from TIA:

- [General --no-coverage validation](infection/no-coverage-validation.md).
- [PHPUnit configuration overrides](infection/configuration-overrides.md).
- [Legacy short-option parsing](infection/legacy-option-parsing.md).
- [The initial-test command's source filtering](infection/initial-test-source-filtering.md).

Earlier notes mentioned “security issues” without a threat, finding or reproduction.
This remains an unspecified review question rather than a confirmed vulnerability.
There is no scenario.

## Recording prerequisites and coverage enforcement

| Requirement | Observed execution | Declared targets |
| --- | --- | --- |
| PHPUnit supporting TIA | Required. The current gate matches only `13.5-dev`; this version string does not identify the pinned commit. | Same. |
| Persistent, writable cache | Required to retain new recordings. | Same. |
| Enabled test-run history | Required for selection. | Same. |
| Non-empty source/coverage filter | Required for recording. | Required to resolve targets. |
| Coverage driver and collection | Required for recording, not selection from existing data. | Not needed to derive dependencies; needed if Infection generates fresh coverage. |
| Mandatory coverage metadata | Not required. | Required for unsized tests and every effective size-specific requirement. |
| Strict coverage checking | Not required for recording. | Recommended. Its absence produces a warning but does not disable derivation. The warning-failure policy can still fail the run. |

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

## What invalidates a recording

PHPUnit checks recording format, PHP/PHPUnit versions, strategy, and execution assumptions
before selection. Missing, unreadable, or malformed data also cannot be reused. Automatic
selection then compares dependency contents (`xxh128`), not timestamps or Git state.
Changed, missing or unreadable dependencies select affected tests. Unexplained
changes to source or watched files can select all tests. Explicit paths replace this content
comparison, but retain the recording-compatibility checks. Repeated explicit paths and paths
from list files form a union; they can identify source files, tests, fixtures or directories.

History stores defects and timings separately from impact dependencies; the impact-settings
hash does not validate the history file. Previously unsuccessful, unknown, and unrecordable
tests remain selected. Tests not executed cannot refresh their observed dependencies.

Execution assumptions include configured PHP values, suite bootstraps, extensions, process
isolation, and global/static backup settings. Reporting, output, failure policy, and order
are excluded. Arbitrary database state and ambient environment values are not fingerprinted.
Behat [04][f04] covers selected invalidation cases, not this entire matrix.

## Infection's generated PHPUnit configuration

[`InitialConfigBuilder`][initial-builder] writes `phpunitConfiguration.initial.infection.xml`
for the initial test run. These transformations were checked in code. Behat exercises
their combined effect, not each transformation independently.

| Setting | Transformation |
| --- | --- |
| Formatting and paths | Reserialise XML; convert paths in the root bootstrap, matched suite exclusions and `directory`/`file` nodes to absolute paths. |
| Source includes | On PHPUnit >=12, preserve existing includes or supply Infection's configured directories. Older versions can narrow them to mutation targets. |
| Bootstrap | Preserve project bootstrap contents. Only mutant runs use Infection's generated interceptor bootstrap. |
| Order | Default to random order when unset, and enable dependency resolution if also unset; supported older versions use defects plus random order. |
| Failure policy | Set `stopOnDefect` on PHPUnit >=10, otherwise `stopOnFailure`; default missing `failOnRisky` and `failOnWarning` to true where supported. |
| Output | Disable colours/stderr; remove printer and configured logging/coverage reports. Supply fresh XML coverage and JUnit destinations through CLI arguments. |
| TIA with fresh coverage | Enable recording/history, default derivation off, and use `.infection/phpunit`, outside the cleaned temporary directory. The impact query is a CLI argument. |
| Other runs | Disable result caching/history; remove history-dependent order on PHPUnit >=13.3. |

Mutant XML removes impact-recording attributes, and mutant commands remove recording and
selection options, including switches without values. [03][f03] checks both strategies,
the tests actually loaded and executed for mutants, unchanged shared impact-data and
history hashes, and subsequent warm reuse.

## Verification and limits

Passing scenarios cover cold/warm reuse within Infection, new tests and source files,
source edits, strengthened assertions, and retention across partial runs ([01][f01], [02][f02]).
Both recording strategies preserve mutant isolation and subsequent warm reuse ([03][f03]);
the fixture has one covering test per mutant, so it does not prove ordering among several tests.
Automatic activation, CLI opt-outs, metadata prerequisites, and the distinction between
declared targets and executed coverage are covered in [05][f05].

Missing, empty, and version-incompatible recordings fall back and become reusable ([04][f04]).
Execution-setting, bootstrap, and lock-file edits invalidate recordings when generated XML
stays within the project.
The unrecorded-source scenario also runs all tests, but reports a different explanation
from the one expected: configuration identity invalidates the recording before the
unknown-path check. The scenario therefore does not currently isolate unknown-path fallback.

Whole-XML hashing is obsolete: [the upstream response][upstream-response] replaced it with
selected execution assumptions and invalidation reasons. Report-formatting differences
therefore no longer cause invalidation, but this does **not** establish that recordings
are interchangeable across configuration locations.

The pinned `13.5-dev` results above supersede the observations for `09b54d872` and
`5f4f80f15`/`13.4-dev`. Earlier claims of shared-cache reuse and safe XML relocation no
longer hold generally. The assertion that changed tests had no scenario is also obsolete.
Earlier manual comparisons reported additional covering tests with unchanged MSI; the
current blocked scenarios stop before their full-run comparisons.

The intended matrix includes projects with and without TIA, a single run (such as CI),
stronger tests after an escaped mutant, and added source/tests. [01][f01]–[05][f05] cover these
categories, but do not form a complete cross-product or a CI-environment test.

`logs.execution` captures initial identities, line-to-test coverage, generated/evaluated
mutations, commands, XML, and cache snapshots; see [the report contract](../execution-report.md).
The PHPUnit extension adds actual loaded/executed identities and effective configuration.
Passing development-cycle controls compare coverage, mutation hashes/statuses, and MSI in
the same project directory, using snapshots and `--do-not-record-test-impact-data` to disable
recording and automatic selection. This avoids path/hash normalisation; timings are excluded.

A cache file or enabled setting does not prove warm reuse. Identifying which test killed a
mutant requires test-framework failure output. The fixture selects two tests on a cold run
and one on a warm run, preserving the results. The [cache-fallback document](infection/cache-fallback.md)
records the obstruction failures and their limits.

No upstream messages or bug reports were posted during these documentation audits.

The Behat suite exercises positional source selection. Git-diff and line restrictions,
supplied coverage, and configuration overrides are not covered by these scenarios.

## Reproduce

From `tests/e2e/PHPUnit_TIA`, with PHP 8.4.1+, a coverage driver, and installed dependencies:

```sh
php vendor/bin/behat --xdebug
php vendor/bin/behat --xdebug --profile=blocked
```

The default profile excludes `@skip`; the blocked profile executes those scenarios and
their assertions. A scenario that stops at an earlier failure does not verify
later assertions. Generated projects and logs remain under `var/behat/scenarios/`.
External-XML artefacts remain under `var/behat/external/`. Append a feature path or `--name`
to run selected scenarios in either profile. Setup details are in the
[fixture README](../../tests/e2e/PHPUnit_TIA/README.md).

[f01]: ../../tests/e2e/PHPUnit_TIA/features/01-initial-run.feature
[f05]: ../../tests/e2e/PHPUnit_TIA/features/05-configuration-and-boundaries.feature
[selectors]: ../../tests/e2e/PHPUnit_TIA/features/05-configuration-and-boundaries.feature#L114
[empty]: ../../tests/e2e/PHPUnit_TIA/features/05-configuration-and-boundaries.feature#L156
[f04]: ../../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature
[initial-builder]: ../../src/TestFramework/PhpUnit/Config/Builder/InitialConfigBuilder.php
[f03]: ../../tests/e2e/PHPUnit_TIA/features/03-mutant-isolation.feature
[f02]: ../../tests/e2e/PHPUnit_TIA/features/02-development-cycle.feature
[upstream-response]: https://github.com/sebastianbergmann/phpunit/pull/6919#issuecomment-5844689848
