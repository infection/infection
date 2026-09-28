# PHPUnit Test Impact Analysis options

This reference describes the feature-branch snapshot reviewed in
[the original integration sketch](phpunit-test-impact-analysis.md):
[PHPUnit PR #6919](https://github.com/sebastianbergmann/phpunit/pull/6919), commit
`09b54d872f5d2bb764c07617b8cf28c6fb6ce41f`. It does not describe a guaranteed released
PHPUnit API. For subsequent compatibility and diagnostic changes, see
[the current findings](phpunit-tia-findings.md).

The options cover three steps: build a dependency map, select tests from it, and explain
that selection.

## Build the impact data

| Option | Behaviour |
| --- | --- |
| `--record-test-impact-data` | Records which files each test actually executes, using a coverage driver. Saves that information for later selection. |
| `--derive-test-impact-data-from-coverage-targets` | Builds the dependency map from declared coverage targets, both `Covers*` and `Uses*` metadata, instead of observed execution. Implies impact recording. |
| `--do-not-record-test-impact-data` | Disables impact recording, including derivation from coverage targets. |
| `--do-not-derive-test-impact-data-from-coverage-targets` | Disables the metadata-based approach. If recording remains enabled, PHPUnit uses observed execution. |

The first two are alternative sources of dependency information:

- **Observed execution** learns what tests execute, but requires a coverage driver and
  running the tests.
- **Declared targets** need no coverage driver to build the map, but accuracy depends on
  complete declarations. PHPUnit requires coverage metadata to be mandatory for all tests
  (`requireCoverageMetadata` and all effective size-specific requirements); otherwise it
  warns and disables derivation. It also warns when strict coverage checking is disabled,
  but that warning alone does not disable derivation.

Recording alone does not narrow the test run. A full suite run can build a map for
subsequent runs.

Two supporting options matter: `--cache-directory` supplies persistent storage, and
`--record-test-run-history` preserves test outcomes. Impact selection requires a cache
directory and enabled history; previously unsuccessful tests are retained in the selection.
Selection can read a compatible existing recording without collecting coverage again.
PHPUnit's longstanding `--no-coverage` option disables observed-execution recording but
allows metadata-derived recording. It differs from `--do-not-record-test-impact-data`,
which disables both recording strategies.

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

The explicit paths can name source files, test files, or directories. The two explicit-path
options can be combined; their paths are merged into a union of affected tests. Relative paths
are resolved against the current working directory, including paths read from a list file.

Explicit paths replace automatic change detection; they do not supplement it. This is
the source of the changed-test concern in the findings: supplying only `src/A.php` can
omit an existing test that changed to cover A after the recording.

## Inspect the decision

`--explain-impacted` reports which tests would run and why, without executing them.

```bash
# Explain automatically detected changes
phpunit --explain-impacted

# Explain selection for an explicit source file
phpunit --explain-impacted --impacted-by src/A.php
```

It uses the same selection logic as an actual run. Reasons include dependency on a changed
file, missing information about a test, a previous unsuccessful outcome, or another selected
test depending on it.

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

A missing or invalid recording falls back to all otherwise eligible tests. Unknown tests
and certain changes the recording cannot account for also trigger conservative selection.

## Relevance to Infection

`--impacted-by-file` can select the initial coverage tests for the source files Infection
intends to mutate. XML coverage and JUnit remain necessary, and TIA should stay out of
mutant execution. The experimental integration now automates that query. The explicit-change-set
semantics around changed tests remain a release blocker; see [the findings](phpunit-tia-findings.md).
