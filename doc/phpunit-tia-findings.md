# PHPUnit TIA: Infection integration findings

Reviewed against [PHPUnit PR #6919](https://github.com/sebastianbergmann/phpunit/pull/6919),
commit `5f4f80f15f1bb1907e6ba277f2cec0756fbb8046` (rechecked on 2026-09-27).

## Verdict

PHPUnit's Test Impact Analysis looks suitable for reducing Infection's initial test run.
The [end-to-end scenario](../tests/e2e/PHPUnit_TIA/README.md) demonstrates that a warm
recording reduces two initial tests to one, preserves line-to-test coverage for the selected
source, and kills the same mutant. TIA stays out of mutant execution. Infection's coverage
parsing and mutation pipeline need no changes.

The experimental integration now enables recording and supplies the impact query from
Infection's resolved source scope automatically. The fixture exercises generated initial XML,
both dependency strategies, and explicit opt-out. This is not a performance benchmark or a
proof that stale recordings are safe; remaining blockers are marked as TODOs in the code.
The earlier [integration sketch](phpunit-test-impact-analysis.md) provides design context.

## Follow-up to Sebastian's response

[The upstream response](https://github.com/sebastianbergmann/phpunit/pull/6919#issuecomment-5844689848)
replaces whole-XML hashing with execution-setting compatibility and adds invalidation reasons.
The updated executable fixture verifies:

- A plain PHPUnit observed recording selects one initial test in Infection, with identical
  selected-source coverage and the same killed mutant.
- Infection's observed recording selects one test in plain PHPUnit using the original XML.
- Moving generated XML within the project and changing colours still selects one test.
- Changing a PHP execution setting runs both tests and reports configuration invalidation.
- Warm selection displays when the recording was made.

Both directions explicitly use the same absolute cache directory. Automatic adoption of the
project's configured cache remains integration work. The initial bootstrap is the project's
bootstrap, so Infection's mutant interceptor does not invalidate initial-run recordings.
These checks resolve the whole-XML compatibility blocker for this fixture. They do not
resolve changed-test safety, lock discovery outside the project, or supplied-coverage policy.

## Decisions and open design choices

Keep this document current as integration decisions are made. The
[PHPUnit option reference](phpunit-tia-options.md) describes the reviewed options.

Agreed direction:

- TIA applies only to the initial test run, not mutant execution.
- Infection should attempt to enable and use TIA whenever supported and safe, even if the
  project has never enabled it. Prior project-side setup or recording is not a prerequisite.
  This supersedes the suggestion to make the integration opt-in initially. Missing
  prerequisites must be distinguished from a cold recording that can be populated by a
  full initial run.
- Users should be able to choose either observed-execution dependencies or dependencies
  derived from PHPUnit's declared coverage targets. Default to observed execution: Infection
  still needs coverage for orchestration. Choosing declared-target dependencies changes
  initial-test selection. Infection still needs coverage reports: generate them during the
  initial run unless the user supplies existing reports through `--coverage`.
- Expose this choice through Infection's `--test-framework-extra-args` option or
  `testFrameworkExtraArgs` configuration entry, using PHPUnit's
  `--derive-test-impact-data-from-coverage-targets` flag to select declared targets.
  This replaces the earlier proposal to use PHPUnit XML as the strategy selector; no
  dedicated Infection strategy setting is needed. Impact options supplied this way must
  apply only to the initial run.
- The observed-coverage default is a deliberate Infection policy, even though PHPUnit
  can derive TIA dependencies without observing execution. Users may opt out of that
  dependency-recording strategy to avoid its additional cost by explicitly choosing
  declared targets. This does not disable initial-run coverage collection needed by
  Infection when generating fresh reports, and does not imply automatically enabling
  `requireCoverageMetadata`.
- `mapSourceClassToTestStrategy` is precedent for supporting different selection strategies,
  not a strategy to retain in the proposed design. Its removal and migration details remain
  to be worked out.
- Explicit test restrictions take precedence over automatic TIA selection. When both source
  and test paths are supplied, source paths limit mutation generation and test paths limit
  initial-test execution. Do not intersect that explicit test selection with a TIA query:
  an older recording could otherwise omit tests the user specifically requested. For example,
  `infection src/Calculator.php tests/CalculatorTest.php` mutates only the selected source
  and initially runs only the selected test file. This is an Infection design decision,
  not a PHPUnit requirement.

## Selection policy

PHPUnit's explicit impact queries accept source files, test files, and directories. Repeated
`--impacted-by` arguments and `--impacted-by-file` lists form a union of affected tests.
`--only-impacted` instead discovers changes since the recording. Explicit paths replace
that discovery; they do not supplement it.

Automatic discovery does not use Git. With no explicit paths, `Selector::explain()` calls
`Recording::testsAffectedByWhatChanged()`, which compares current dependency-file hashes
with the versions recorded for each test. `changeNothingIsKnownAbout()` also checks the
current source-file list and changes that the recording cannot account for. The paths come
from the recording and configured source scope, not a Git diff.

Infection uses `--impacted-by-file` for mutation targets because unchanged source can still
be selected for mutation. A line restriction is passed as the containing files, leaving
Infection to restrict mutation generation to the selected lines.

| Infection selection | Initial-run behaviour |
| --- | --- |
| Restricted source scope, no explicit test restriction | Query TIA with the resolved source-file list. |
| Explicit test paths or filters | Honour the requested tests without adding an automatic TIA query. |
| Both source and test restrictions | Limit mutations to the source scope and execution to the requested tests; do not intersect the test selection with TIA. |
| Neither restriction | Run the full configured initial suite; recording can prepare future restricted runs. |

The explicit-test precedence is an agreed design decision and a candidate for an ADR;
no ADR has been written. User-selected test paths mean tests to execute. Changed test files
that might need to be added for recording freshness are a separate concern, not interchangeable
with those selections. Explicit user-supplied impact queries remain user-controlled.

## Coverage enforcement options

These PHPUnit options default to false and do not choose a TIA strategy:

| Option | Check |
| --- | --- |
| `requireCoverageMetadata` | Require a declared coverage target or explicit `CoversNothing`. `Uses*` alone is insufficient. This check does not require coverage collection. |
| `requireCoverageContribution` | While collecting coverage, require a test to contribute at least one executed line after filtering; explicit coverage opt-outs are exempt. The line need not be unique to this test. |
| `beStrictAboutCoverageMetadata` | During coverage collection, check execution outside declared covered or used code, subject to PHPUnit's strict-coverage rules. |

A metadata declaration does not prove that the test executes its target, and contributing
coverage does not prove that declarations are complete. Violations of these checks can make
a test risky; `failOnRisky` separately controls whether that makes the run unsuccessful.
Strict coverage checking is the relevant validation for dependencies derived from declarations.
Infection should preserve the project's enforcement policy, rather than silently making
otherwise valid tests risky to enable TIA.

## Prerequisites for automatic use

These requirements come from the reviewed PHPUnit snapshot; a released version gate is
still to be established. The table distinguishes recording requirements from selection:
reading a compatible existing impact recording does not require recording a new one.

| Requirement | Observed execution | Declared coverage targets |
| --- | --- | --- |
| PHPUnit implementing the TIA options | Required | Required |
| Configured, writable cache directory retained between runs | Required for reusable recordings | Required for reusable recordings |
| Test-run history enabled | Required for impact selection | Required for impact selection |
| Non-empty source/coverage filter | Required | Required |
| Working coverage driver and coverage collection enabled | Required for recording | Not required for deriving dependencies; needed only if Infection generates fresh coverage |
| Coverage metadata required for all test sizes and unsized tests | Not required | Required; PHPUnit warns and disables derivation otherwise |
| Strict coverage metadata checking | Not required for dependency recording | Recommended to validate declarations; absence emits a warning rather than disabling derivation |

The metadata gate checks `requireCoverageMetadata` and all three effective
`requireCoverageMetadataOnSmallTests`, `requireCoverageMetadataOnMediumTests`, and
`requireCoverageMetadataOnLargeTests` settings. `requireCoverageMetadata="true"` satisfies
it unless a size-specific override disables the requirement. `requireCoverageContribution`
is not a TIA prerequisite. Infection should not silently impose metadata requirements on
the user's tests merely to enable the declared-target strategy.
The warning about disabled strict coverage checking can still fail a run when PHPUnit's
warning-failure policy is enabled, even though derivation itself remains enabled.

Infection can supply the cache, history, and recording configuration. A pre-existing
recording, Git repository, or `composer.lock` is not required. Missing, invalidated, or
strategy-incompatible recordings cause full-suite fallback. Unknown/unrecordable tests
remain selected rather than blocking TIA for the entire suite. Compatible execution settings and
paths are needed for useful reuse; validity includes PHP/PHPUnit versions, execution settings,
bootstrap, source scope, and the dependency lock file when present.

Safe automatic selection also requires resolving the changed-test concern below and
preserving dependency invalidation with generated configuration. These are integration
correctness requirements, not additional PHPUnit switches the project can simply enable.

## Implementation progress

### Supplied coverage: agreed behaviour and open policy

Infection sets `$skipCoverage = $existingCoveragePath !== null`; the new `$collectCoverage`
parameter is its inverse. It means generating fresh coverage during this initial run, not
whether Infection needs coverage data or which TIA dependency strategy is selected.
`infection --coverage=<directory>` reuses existing reports but still runs initial tests.
Skipping those tests requires the separate `--skip-initial-tests` option and supplied reports;
TIA has no initial run to optimise in that case.

**Decision:** when Infection uses supplied coverage reports, add PHPUnit's `--no-coverage`
to the initial command to prevent collecting coverage again. Currently Infection only
removes configured reports and omits its coverage/JUnit output arguments; this does not
prevent TIA recording or extensions from requesting coverage. Adding the flag is agreed
but not implemented yet. It preserves PHPUnit's ability to derive dependencies from
declared targets without a coverage driver.

When TIA is supported, coverage reports are supplied, and initial tests still run, the
proposed policy is to avoid collecting coverage again while preserving useful TIA selection:

| Project TIA strategy | Proposed initial-run behaviour |
| --- | --- |
| Observed execution | Select from a compatible existing impact recording without refreshing observed dependencies; fall back to the full eligible initial suite when none is usable. |
| Declared targets | Keep metadata-derived recording and selection, subject to its prerequisites; it requires no coverage collection. |
| No TIA | Run the ordinary initial suite without enabling coverage collection or silently switching to declared targets. An explicit disable remains respected. |

This revises the earlier suggestion to disable all TIA with supplied reports; it is not
implemented yet. PHPUnit's `--no-coverage` disables observed recording but preserves
metadata-derived recording. `--do-not-record-test-impact-data` disables both, so it is not
an equivalent override. Selection itself requires history and a cache, not active impact
recording. Reuse still depends on configuration compatibility and the changed-test
safeguards; supplied coverage XML is not itself a PHPUnit TIA recording.

The current early return for `!$collectCoverage` is insufficient to enforce this policy:
it can leave project recording enabled while generated initial XML disables test-run history.

### Current implementation

The generated initial configuration now enables observed-execution impact recording and
history for the fixture's `13.4-dev` snapshot when collecting coverage. Its persistent cache
is `<project>/.infection/phpunit`, separate from PHPUnit's normal cache and Infection's
temporary directory. CLI extra arguments can select declared targets or disable recording.
Mutant configurations remove impact-recording attributes, and mutant commands strip TIA
recording and selection arguments, including valueless switches.

For a restricted mutation source scope, Infection writes the resolved file list and adds
`--impacted-by-file` automatically. Explicit test restrictions and impact queries suppress
the automatic query. Separate option values also conservatively suppress automatic selection;
the implementation does not duplicate PHPUnit's complete argument grammar. Configuration
overrides through extra arguments (`--configuration`, `-c`, and `--no-configuration`) are
temporarily rejected on the TIA path because they bypass Infection's generated XML. Open a
separate bug issue and fix configuration selection independently; this guard is temporary.
Unrestricted source runs record the full initial suite. Declared-target mode without mandatory
metadata logs a notice and falls back without TIA; recording/history opt-outs also disable
automatic selection.

The scripted fixture now uses ordinary `phpunit.xml` and Infection's generated configuration.
It verifies cold/warm initial test counts of 2/1 for both observed and declared dependencies,
and 2 tests when recording is disabled. All runs kill the same mutant without TIA leaking
into mutant execution. Missing-metadata fallback was also checked with an initial run.

Code TODOs retain the changed-test and provider safeguards, the actual released version gate,
lock-file discovery outside the project, and cache failure diagnostics.
Automatic selection is experimental and restricted to the reviewed `13.4-dev` version string;
that string alone does not identify the pinned feature-branch commit.

## Main correctness concern: changed tests

Earlier probes against `09b54d872` showed that explicit source targeting can omit
an existing test whose implementation changed after recording:

1. Record a suite where one test covers A and another covers C.
2. Change the existing test for C to cover A as well, keeping its test ID.
3. Select only A through `--impacted-by-file`.
4. The changed test can be omitted, although a full run produces additional coverage for A.

The end-to-end scenario keeps tests unchanged and does not exercise this case. Inspection
of `Selector::explain()` at the updated pin confirms that explicit paths still call
`testsThatDependOnAnyOf()` instead of `testsAffectedByWhatChanged()`. Sebastian's response
does not claim a fix for this selection contract.

PHPUnit's explicit paths may intentionally represent the complete change set. Infection's
paths instead represent the source the user wants to mutate; they can include unchanged
source and omit tests changed since recording. These contracts are different.

Incomplete initial coverage could cause Infection to miss mutations or covering tests.
Before this experimental integration is released, either PHPUnit must retain changed-test and
data-provider safeguards when explicit source paths are supplied, or Infection must detect
such changes and conservatively run the full initial suite. Appending test paths from the
current Git diff alone does not cover every change since an older recording.

## Cache and configuration concerns

PHPUnit separates test-run history (defects and timings by test ID) from impact dependencies.
The execution-settings hash validates the impact recording, not the ordinary result-history file.
TIA retains tests with recorded unsuccessful outcomes in its selection.

Impact reuse is checked at two levels in the reviewed snapshot:

- `TestImpactDataFile::parse()` rejects incompatible recording-format, PHPUnit, or PHP
  versions and changed assumptions: execution settings, bootstrap-file contents,
  effective source include/exclude settings, and the located `composer.lock`. Missing,
  unreadable, or malformed recordings also cannot be used. `recording()` additionally checks
  that the recording's dependency strategy matches the requested strategy.
- With automatic change detection, `Recording::isCurrent()` compares current dependency
  hashes with the versions referenced by each test. File contents are hashed with `xxh128`,
  not compared by modification time or Git state. Changed or unreadable/missing dependencies
  cause affected tests to run; unknown source changes can cause full-suite fallback. Explicit
  impact paths replace this per-dependency change detection with path-based selection, while
  the whole-recording compatibility checks still apply.

There is no general fingerprint of external state such as a database or environment-variable
values in these recording assumptions. Configured `<php>` values and CLI PHP overrides
are included, along with suite bootstraps, loaded extensions, process isolation, and global
or static-property backup settings. Output, reporting, failure policy, and order are ignored.

For PHPUnit >= 12, `InitialConfigBuilder` preserves an existing `<source><include>`
instead of narrowing it to Infection's mutation scope. If it is absent, Infection adds
its configured source directories. Older PHPUnit versions can have their coverage include
list narrowed to selected mutation files. Infection also changes report destinations,
execution order defaults, stop-on-failure behaviour, and history settings, so preserving
the source scope does not mean the generated configuration is identical or that its TIA
recording is interchangeable in every project. The fixture now verifies interchangeability
when execution settings, bootstrap, source, lock file, strategy, and runtime match.

The initial run does not generate a custom bootstrap: it preserves the project's bootstrap
file and rewrites its configured path to an absolute path. The generated interceptor
bootstrap is exclusive to mutant runs. Consequently, initial-run bootstrap file contents
are unchanged by Infection; rewriting its path no longer changes the recording assumptions.

`InitialConfigBuilder` writes a separate `phpunitConfiguration.initial.infection.xml`; it
does not edit the project's file. Its initial-configuration transformations are:

| Setting | Transformation |
| --- | --- |
| XML formatting | Parse and reserialize with formatted output and without preserving whitespace. |
| Paths | Absolutize the root bootstrap path, test-suite exclusions matched by the manipulator, and `directory`/`file` elements. |
| Coverage/source includes | Preserve existing includes on PHPUnit >= 12; supply configured sources if absent. Older versions may narrow includes to mutation targets. |
| Test order | If unset, add random ordering (defects plus random on supported older versions), and dependency resolution if also unset. |
| Failure policy | Set `stopOnDefect=true` on PHPUnit >= 10, otherwise `stopOnFailure=true`; default missing `failOnRisky` and `failOnWarning` to true where supported. |
| Output | Set `colors=false` and `stderr=false`; remove `printerClass`, `/phpunit/logging`, and `/phpunit/coverage/report`. |
| History without automatic recording | Disable result caching/history, removing history-dependent ordering on PHPUnit >= 13.3. |
| Experimental TIA with fresh coverage | Enable history and impact recording, set declared-target derivation false by default, and use `<project>/.infection/phpunit` as the cache. Extra arguments can override the dependency strategy. |

Coverage XML and JUnit destinations are supplied as CLI arguments when generating reports.
The automatic impact-file query is also a CLI argument, not a rewritten test-suite node.
Formatting and output changes no longer invalidate compatible impact data. Resolved paths
must still refer to the same bootstrap, source scope, and dependency lock file.

| Concern | Implication |
| --- | --- |
| Shared cache | Equivalent project and generated configurations can now reuse recordings. Infection still defaults to its own persistent cache; automatically resolving a project cache remains to be implemented. |
| Generated configuration location | The fixture validates reuse after moving generated XML within the project. Execution settings and resolved bootstrap/source/dependency assumptions must remain equivalent. |
| `composer.lock` is located relative to the configuration directory and its ancestors | A temporary configuration outside the project may miss the lock file. Infection must preserve dependency invalidation; a private cache alone does not solve this. |
| Initial-run history is now enabled for the reviewed TIA snapshot | Other versions retain the previous initial-run behaviour; the final released version gate remains a TODO. |
| Recording has a runtime cost | Measure complete cold and warm Infection runs on representative projects before release. The small fixture is a functional demonstration, not a benchmark. |

A missing or invalid recording already falls back to the full suite. The changed-test
concern is different: a usable recording may be stale for Infection's source-targeting use case.

## Demonstration limitations and integration boundaries

- PHPUnit's `--no-coverage` extra argument is allowed when Infection's `--coverage`
  option supplies existing reports. Otherwise it is rejected before the TIA version gate.
  Accepting it without supplied reports is a pre-existing bug. File a separate bug report
  and move validation to extra-argument handling later; the current assertion is a temporary
  guard. Supplying reports still uses Infection's existing coverage validation. The option
  predates TIA: it already exists in [PHPUnit 5.7.27](https://github.com/sebastianbergmann/phpunit/blob/5.7.27/src/TextUI/Command.php).
- `initial-test:run` must mirror the run command's source filtering: no filter by default,
  with positional paths and explicit source/Git filters supported. Its unconditional Git
  filter caused `NoSourceFound` for untracked fixture sources before PHPUnit ran; the
  command now uses the run command's shared filter handling. The divergence dates to the
  command's introduction in `1fdaed86b` (2026-03-06, PR #2762), which already constructed
  the Git filter unconditionally. The [PR description](https://github.com/infection/infection/pull/2762)
  states that the command supports the regular command's options and lists only logging
  and automatic debug mode as differences; mandatory Git filtering was not a stated intent.
  **Follow-up:** extract this command-filter fix and its regression test as a separate
  change from the PHPUnit TIA work.
- The fixture exercises automatic source-path queries through positional source selection.
  Git-diff and legacy `--filter` selections feed the same resolved source-file list.
- The earlier duplicate-configuration workaround is no longer needed by the fixture.
  Configuration overrides are temporarily rejected on the TIA path. Open a separate bug
  issue for the generated-configuration bypass and fix it independently.
- Automatic TIA selection should apply only when source scope is narrowed and the user has
  not already restricted tests. Explicit test filters should not be silently intersected
  with another selection. Migration from `mapSourceClassToTestStrategy` remains open.
- XML coverage and JUnit remain necessary. TIA does not replace their use for mutation
  generation, covering-test selection, timings, or other information.

## Remaining upstream feedback

Configuration compatibility and cold/warm diagnostics now work in the fixture. Follow-up
should focus on the explicit-source selection contract: source selected for mutation is not
necessarily the complete set of changes since recording. Changed-test and data-provider
freshness remain unresolved. Dependency-lock discovery outside the project also needs a
supported integration approach before using arbitrary temporary directories safely.

No messages or bug reports were posted as part of this follow-up.
