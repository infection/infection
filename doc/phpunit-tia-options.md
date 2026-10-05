# PHPUnit Test Impact Analysis options

This overview describes the pinned snapshot of
[PHPUnit PR #6919](https://github.com/sebastianbergmann/phpunit/pull/6919), commit
[`e21b72d4ac3a9d9351eaa065638dd5e6259879bd`](https://github.com/sebastianbergmann/phpunit/tree/e21b72d4ac3a9d9351eaa065638dd5e6259879bd),
which reports `13.5-dev`. The feature is not merged; these options are experimental.

The options cover three steps: build a dependency map, select tests from it, and explain
that selection.

## Build the impact data

| Option | Behaviour |
| --- | --- |
| `--record-test-impact-data` | Records which source files each test executes, using a coverage driver. Also tracks test code and fixture dependencies. |
| `--derive-test-impact-data-from-coverage-targets` | Builds the dependency map from declared coverage targets, both `Covers*` and `Uses*` metadata, instead of observed execution. Implies impact recording. |
| `--do-not-record-test-impact-data` | Disables impact recording, including derivation from coverage targets. |
| `--do-not-derive-test-impact-data-from-coverage-targets` | Disables the metadata-based approach. If recording remains enabled, PHPUnit uses observed execution. |

The first two are alternative sources of dependency information:

- **Observed execution** learns what tests execute, but requires a coverage driver and
  running the tests. For tests running in a separate process, loaded source files also
  count as dependencies.
- **Declared targets** need no coverage driver to build the map, but accuracy depends on
  complete declarations. PHPUnit requires coverage metadata to be mandatory for all tests
  (`requireCoverageMetadata` and all effective size-specific requirements); otherwise it
  warns and disables derivation. It also warns when strict coverage checking is disabled,
  but that warning alone does not disable derivation.

Recording alone does not narrow the test run. A full suite run can build a map for
subsequent runs. Both strategies need a non-empty source filter. Tests without usable
coverage targets, including `CoversNothing` tests, remain unknown in declared-target
recordings and are therefore selected conservatively.

Two supporting options matter: `--cache-directory` supplies persistent storage, and
`--record-test-run-history` preserves test outcomes. Impact selection requires a cache
directory and enabled history; previously unsuccessful tests are retained in the selection.
Selection can read a compatible existing recording without collecting coverage again.
PHPUnit's longstanding `--no-coverage` option disables observed-execution recording but
allows metadata-derived recording. It differs from `--do-not-record-test-impact-data`,
which disables both recording strategies.

The XML equivalents on `<phpunit>` are `recordTestImpactData`,
`deriveTestImpactDataFromCoverageTargets`, `recordTestRunHistory`, and `cacheDirectory`.
CLI settings override their XML counterparts. Impact recording and derivation default
to `false`.

## Include dependencies beyond source code

PHPUnit also tracks the files that define tests, including parent classes, traits, and
data providers. For inputs that execution cannot reveal:

- `#[UsesFixture('input.json')]` declares a file or directory dependency on a test class,
  test method, or data-provider method. Relative paths start from the PHP file containing
  the attribute. It works with both recording strategies.
- `$this->registerFixture($path)` registers a dependency discovered during a test run
  for observed-execution recording. Relative paths start from the working directory.

Fixture directories are hashed recursively, so added, removed, or changed files count
as changes to the dependency.

PHP helper files in configured test-suite directories are watched automatically. Add
other inputs to `<testImpactAnalysis><watch>` in the PHPUnit XML configuration:

```xml
<testImpactAnalysis>
    <watch>
        <directory suffix=".json">fixtures</directory>
        <file>config/testing.ini</file>
    </watch>
</testImpactAnalysis>
```

These paths are relative to the XML configuration file. Watched directories default to
the `.php` suffix. Watching ensures automatic change detection notices files even when
no test has a recorded dependency on them; such changes cause a full-suite fallback.

## Choose what counts as changed

| Option | Behaviour |
| --- | --- |
| `--only-impacted` | Detects changes by comparing current files with the recorded state, then runs affected tests. |
| `--impacted-by <path>` | Treats a supplied file or directory as changed and runs affected tests. Can be repeated. |
| `--impacted-by-file <file>` | Reads the supplied change set from a file, one path per line. Use `-` to read standard input. |

The distinction is automatic change detection versus an explicit change set:

- `--only-impacted` asks: “What changed since the recording?”
- `--impacted-by src/A.php` asks: “Which tests are affected by this path?” The file does
  not actually need to have changed. This option already enables selection; adding
  `--only-impacted` is unnecessary.

The explicit paths can name source files, test files, fixtures, or directories. The two
explicit-path options can be combined; their paths are merged into a union of affected
tests. Relative paths are resolved against the current working directory, including
paths read from a list file.

Explicit paths replace automatic change detection; they do not supplement it. Supplying
only `src/A.php` can therefore omit an existing test that changed to cover A after the
recording, or one whose fixture changed. Adding `--only-impacted` does not combine the
two modes. Existing suite, group, and filter restrictions still apply.

## Inspect the decision

`--explain-impacted` reports which tests would run and why, without executing them.

```bash
# Explain automatically detected changes
phpunit --explain-impacted

# Explain selection for an explicit source file
phpunit --explain-impacted --impacted-by src/A.php
```

It uses the same selection logic as an actual run. Reasons include dependency on a changed
file, missing information about a test, a previous unsuccessful outcome, or dependencies
between tests. Selection includes both tests affected by another selected test and the
prerequisite tests needed to run selected tests.

`--list-tests-that-depend-on <file>` instead inspects the recorded dependency map without
running tests. It reports the recording time and strategy, and distinguishes dependencies
on the file's current contents from dependencies on an older version. This is a recording
lookup, so it does not include all the conservative safeguards used by impact selection.

## When PHPUnit runs everything

A missing, empty, unreadable, or incompatible recording falls back to all otherwise
eligible tests. Compatibility checks cover the PHP and PHPUnit versions, recording
strategy, effective execution settings, bootstrap scripts, first-party source definition,
and the nearest `composer.lock` found in or above the configuration directory (or working
directory when no configuration is used). Changing report paths or presentation settings
alone does not invalidate a recording.

Automatic detection also falls back when source or watched files are new or changed in
ways no recorded dependency explains. Recorded code executed outside any test is treated as a
dependency of every test. An explicit query for an unknown path also triggers a fallback.
PHPUnit reports the reason in its impact summary and explanation output.

Missing prerequisites are errors: selection requires a configured cache directory and
enabled test-run history even when it cannot reuse impact data.

## Typical workflow

Assuming the XML configuration enables history and supplies a cache directory:

```bash
# Initial full run: build the observed dependency map
phpunit --record-test-impact-data

# Subsequent run: detect changes, select tests, refresh their impact data
phpunit --record-test-impact-data --only-impacted

# Or select using an explicit list of files/directories
phpunit --record-test-impact-data --impacted-by-file changed-paths.txt
```

Partial runs refresh the recorded dependencies of tests they run while preserving data
for tests they do not run.

For Infection's integration scenarios and blockers, see [the integration overview and feedback](TIA/README.md).
