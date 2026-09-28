# PHPUnit Test Impact Analysis for Infection's initial tests

Status: historical integration sketch. An experimental implementation now exists; see
[the findings](phpunit-tia-findings.md) for current decisions, implementation status, and
remaining blockers. Proposals below are design context, not a description of current code.
Reviewed against
[PHPUnit PR #6919](https://github.com/sebastianbergmann/phpunit/pull/6919), commit
[`09b54d872`](https://github.com/sebastianbergmann/phpunit/commit/09b54d872f5d2bb764c07617b8cf28c6fb6ce41f),
on 2026-09-22. That checkout identifies itself as 13.4-dev; the release gate is not yet settled.

## Scope

When Infection selects source through `--git-diff-lines`, `--filter`, positional source paths,
or another source selector, use TIA to reduce the **initial test run** if the user has not
already restricted which tests to run. Pass the resolved original source files to PHPUnit
through `--impacted-by-file`. For line selection, the containing files form a safe broader
selection, provided the dependency recording is reliable.

The selected initial tests still generate the normal XML coverage and JUnit reports.
Infection continues to parse those reports for line coverage, test locations, timings, and
other information. Mutation generation, AST enrichment, mutant test selection, process
execution, and scoring keep their existing behaviour.

The executable scenario is `tests/e2e/PHPUnit_TIA`. It now exercises automatic source-scope
wiring and generated initial configurations for both dependency strategies, plus an explicit
TIA opt-out. See its README and the findings for the current validation scope.

## Proposed flow

1. Resolve Infection's source scope using the existing source-selection logic.
2. If there is no narrower source scope, run initial tests as today. Recording can still
   populate the TIA cache if this integration is enabled.
3. If the user already restricted tests, retain that selection without adding TIA. This includes
   PHPUnit `--filter` (distinct from Infection's source `--filter`), test paths, suite/group
   options, and Infection's source-to-test mapping. Ordinary suite definitions and exclusions
   in the project's XML remain the baseline test universe.
4. Otherwise write a non-empty list of absolute selected source paths, one per line, and add
   `--impacted-by-file` to the initial PHPUnit command. Pass the union once, not one query per file.
5. Enable PHPUnit test history and use a persistent Infection-owned cache for this initial-run
   configuration. Record observed impact data while collecting the existing coverage reports.
6. On a cold or invalid cache, PHPUnit runs the full configured suite. It records dependencies
   so later invocations can narrow the initial run. A selected run refreshes entries for tests
   it executes without deleting entries for the others.
7. Consume the resulting reports through the existing Infection pipeline.

Illustrative argv, after Infection generates its initial XML configuration:

```sh
phpunit --configuration /tmp/infection/phpunitConfiguration.initial.infection.xml \
  --cache-directory /project/var/infection/phpunit-impact \
  --record-test-run-history --record-test-impact-data \
  --impacted-by-file /tmp/infection/selected-source-files.txt \
  --coverage-xml /tmp/infection/coverage-xml --log-junit /tmp/infection/junit.xml
```

Implement this as an argv array through the existing command builders. These options belong
only to the initial run, not shared framework options that also reach mutant processes.
Record via CLI so the original XML used to build mutant configurations gains no TIA setting.
The existing per-mutant cache isolation remains in place.

Use explicit paths, not plain `--only-impacted`: Infection may intentionally select unchanged
source, and it already knows the scope. An empty source selection should follow Infection's
existing no-source handling; never pass an empty path list and interpret a zero-test run as a
successful initial validation. Reusing externally supplied coverage should retain the existing
skip-initial-run behaviour rather than introduce an additional TIA run.

## Changes in Infection

| Component | Change |
| --- | --- |
| Source selection / configuration wiring | Provide the resolved source paths and whether the user already selected tests to the PHPUnit initial-run builder. Choose a persistent cache location, distinct from the temporary report directory. |
| `PhpUnitAdapter::getInitialTestRunCommandLine()` | For a compatible PHPUnit and enabled integration, add recording/history/cache options. Add explicit impact paths only for a restricted source scope without an explicit test restriction. Continue requesting XML coverage and JUnit. |
| `ArgumentsAndOptionsBuilder::buildForInitialTestsRun()` | Compose the initial-only options without silently intersecting TIA with the existing source-to-test mapping or user test filters. Reuse the raw option parser. |
| `InitialConfigBuilder` / `XmlConfigurationManipulator` | Account for the current `recordTestRunHistory="false"` setting. An initial-run CLI override works in the experiment; a mode-specific builder setting is another implementation choice. Keep generated XML contents stable between invocations. |
| Cache lifecycle / diagnostics | Explain cold/full fallback, preserve recordings across invocations, and refresh safely when test code or assumptions change. The changed-test case below needs resolution before source-only selection is safe. |

No change to `Tracer`, coverage parsing, `TestLocation`, `Mutation`, mutant configuration,
mutant filtering, or reporter contracts is needed for this feature.

## Cache compatibility and freshness

A recording from a normal project PHPUnit run is not automatically reusable by Infection.
The PR hashes the complete XML contents, bootstrap contents, source declarations, and the
nearest `composer.lock`. Infection rewrites the initial XML, so sharing the user's cache
causes an assumption mismatch even with identical application source.

An Infection-owned persistent cache avoids competing recordings. It must warm against
Infection's generated initial configuration, whose contents must remain stable across runs.
The end-to-end fixture uses an initial-only configuration override to demonstrate TIA today;
it does not validate cache reuse with generated configurations. Report paths passed through
CLI do not alter the XML hash.

The reviewed implementation finds `composer.lock` relative to the configuration directory
and its ancestors. A generated configuration outside the project may miss its lock file.
The production design must preserve dependency invalidation, for example by storing this
configuration under the project or by invalidating the private cache when the project's lock
changes. A private cache alone does not solve this.

Caching is necessary: always using a fresh temporary cache gives a full initial run every time.
Observation also has a cost. The PR reports a 2.6× increase for one coverage run, particularly
from `CoversNothing` tests. Measure the full cold and warm Infection runs before choosing a
default. Declaration-derived data is an alternative for suitable projects, but Infection
would still collect coverage; it does not remove the coverage-driver requirement here.

## Correctness prerequisite: changed tests with explicit source paths

Earlier probes against the pinned PR reproduced a gap that matters to this integration
(the new end-to-end scenario keeps tests unchanged and does not cover this case):

1. Record a passing suite: `ATest` covers A; `BTest` covers B and uses A; `CTest` covers C.
2. Change the existing `CTest::testValue` to cover A as well, without changing its test ID.
3. Request initial tests with `--impacted-by-file` containing only `src/A.php`.
4. PHPUnit selects ATest and BTest but omits the changed CTest. A full initial run includes
   CTest and produces additional coverage for A. Adding CTest's path to the explicit list
   also restores that coverage.

With explicit paths, `Selector` uses recorded dependencies on those paths instead of hash-based
change detection. An existing passing test whose implementation changed is not automatically
selected in this branch. The explicit-path contract may intentionally mean “this is the entire
change set”, but Infection's source selection means “this is the area I want to mutate”. They
are different inputs. This affects source filters as well as git-diff mode, because test edits
may postdate the cached recording without appearing in the selected source list.

Before enabling the integration, either PHPUnit must preserve changed-test/data-provider
safety when source paths are supplied, or Infection must detect such changes relative to its
recording and conservatively refresh with a full initial run. Merely appending test paths from
a current git diff does not cover every change since an older recording. A missing or invalid
recording already has a safe full-suite fallback; this is a usable but stale recording.

This decision is pending contributor input. No production integration is enabled by this sketch.

## Try it yourself

The new scenario runs Infection itself, including mutation generation and evaluation.
Use PHP 8.4.1+ with Xdebug or PCOV and install Infection's dependencies first:

```sh
composer install --working-dir=tests/e2e/PHPUnit_TIA
./tests/e2e_tests bin/infection '^./PHPUnit_TIA$'
```

Or rerun directly after installing dependencies:

```sh
bash tests/e2e/PHPUnit_TIA/run_tests.bash bin/infection
```

The fixture clears its own `var/` directory, then runs Infection twice with
`--filter=src/Calculator.php`. It explicitly passes the matching source path list via
`--test-framework-options`, along with an initial-only XML configuration enabling TIA.

| Assertion | Cold run | Warm run |
| --- | --- | --- |
| Initial tests, checked through JUnit | CalculatorTest and UnrelatedTest | CalculatorTest only |
| Line-to-test coverage for Calculator | Non-empty | Identical to cold run |
| Mutation result | One Plus mutant killed by an assertion failure | Same |
| TIA in mutant execution | Absent | Absent |

Reports and console output are retained under `tests/e2e/PHPUnit_TIA/var/{cold,warm}`.
See the [fixture README](../tests/e2e/PHPUnit_TIA/README.md) for the configuration details.
This demonstrates the desired initial-run behaviour with explicit options; automatic
source selection, generated-configuration cache reuse, and changed-test safeguards remain
integration work described above.

## Feedback for PHPUnit

The supported execution CLI is sufficient for initial-run integration. The former sketch's
ID-export and empty-ID-file findings are unrelated to this workflow and should not be presented
as Infection blockers. Relevant feedback is:

1. Clarify whether explicit paths retain changed-test/data-provider safeguards. The current
   source-only path list can omit a changed existing test and yield incomplete coverage for
   the requested source. An additive “analyse these source paths while retaining test freshness
   checks” contract would directly support Infection.
2. Make cache assumption failures distinguishable from absence. Generated XML currently yields
   “no test impact data has been recorded” even when data exists but the configuration differs.
   Explain configuration/bootstrap/source/dependency/runtime invalidation separately.
3. Document reuse with generated configurations and a private persistent cache, including
   how `composer.lock` is located. Reusing ordinary project recordings would require an explicit
   compatibility contract, not bypassing safety checks.

Relevant upstream code:
[`Selector::explain()`](https://github.com/sebastianbergmann/phpunit/blob/09b54d872f5d2bb764c07617b8cf28c6fb6ce41f/src/Runner/TestImpactAnalysis/Selector.php),
[`Assumptions`](https://github.com/sebastianbergmann/phpunit/blob/09b54d872f5d2bb764c07617b8cf28c6fb6ce41f/src/Runner/TestImpactAnalysis/Assumptions.php),
and [`TestImpactDataFile`](https://github.com/sebastianbergmann/phpunit/blob/09b54d872f5d2bb764c07617b8cf28c6fb6ce41f/src/Runner/TestImpactAnalysis/TestImpactDataFile.php).
The author's existing parallel-runner integration notes also matter if the initial PHPUnit
run uses its native parallel mode; they are already documented upstream.

Suggested message:

> Infection would use TIA to narrow its initial test run when users select source files or
> changed lines. We would pass the resolved source paths through `--impacted-by-file` and still
> request XML coverage and JUnit. Our mutation pipeline would continue to use those reports.
>
> A warm recording successfully reduces the initial suite in our experiment, with identical
> line coverage for the selected source. However, after an existing test changes to cover that
> source, explicitly naming only the source file omits that changed test. Is that deliberate
> for explicit-change-set mode? Could there be a mode that selects dependencies of the named
> source files while retaining the changed-test/data-provider safeguards? Our source scope is
> not necessarily a complete list of changes since recording.
>
> We also generate initial XML, so a project's existing recording fails the configuration hash
> check. A private persistent recording works when generated XML stays identical. Clearer
> invalidation reasons, and guidance on dependency-lock invalidation for generated configs
> outside the project, would help us integrate this safely.
