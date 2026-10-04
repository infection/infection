# PHPUnit TIA for Infection's initial tests

Behat executes all five features under `features/`:

- `01`: Initial recording and warm reuse.
- `02`: Test and source edits during development.
- `03`: Mutant isolation with observed-execution and declared-target recordings.
- `04`: Cache fallback, dependency invalidation, and sharing recordings with PHPUnit.
- `05`: Automatic activation, opt-outs, coverage metadata, and selection boundaries.

Confirmed blockers are tagged `@skip`. The `blocked` profile runs their desired
assertions and is expected to fail. The explicit-selector scenarios are tagged
`@current_behavior`; whether TIA should further narrow those selections remains open.

Repeated Infection runs compare executed tests, Calculator's line-to-test coverage,
generated mutations, detection statuses, and MSI. Timing and process output are not
part of that comparison.

Development-cycle scenarios also compare the updated project's coverage, generated mutations,
detection statuses, and MSI with a full-suite run. This control uses the same scenario directory
and adds `--do-not-record-test-impact-data` to the PHPUnit options, preserving any other
options. This suppresses Infection's automatic impact query. It does not refresh impact data. Keeping paths identical allows direct
comparison of mutation hashes; in-memory snapshots preserve the preceding TIA result.

Project edits use a single diff hunk without file or line-number headers: `-` removes
a line, `+` adds one, and a leading space supplies unchanged context. The original block
must match exactly once, including indentation; missing or ambiguous matches fail before
writing the file. Add context when the same code occurs in several places.

## Run

`behat.yml` enables the progress formatter and strict mode for all profiles.

Use PHP 8.4.1+ with Xdebug or PCOV, Composer, and Infection's installed dependencies:

```sh
composer install --working-dir=tests/e2e/PHPUnit_TIA
bash tests/e2e/PHPUnit_TIA/run_tests.bash bin/infection
```

Or use the e2e runner:

```sh
./tests/e2e_tests bin/infection '^./PHPUnit_TIA$'
```

## Setup and where to look

There are two test layers: Behat runs the scenarios, while PHPUnit runs the small
PHP project that Infection mutates. This fixture has its own Composer dependencies,
including Behat and a pinned PHPUnit build with TIA support. The Infection executable
comes from the repository (or the path passed to the runner), not the fixture's `vendor/`.
The fixture pins PHPUnit PR #6919 at `e21b72d4ac3a9d9351eaa065638dd5e6259879bd`
until a release includes TIA.

The execution flow is:

1. [run_tests.bash](run_tests.bash) selects the Infection executable through
   `TIA_INFECTION` and starts the fixture's Behat with Xdebug preserved.
   [behat.yml](behat.yml) selects the features and wires the contexts to shared services.
2. Before the `Background` runs, `ScenarioProjectContext` recreates a project under
   `var/behat/scenarios/<feature-and-scenario-name>/`. It copies the source, tests,
   PHPUnit extension, installed dependencies, Composer files, and Infection configuration,
   then rebuilds the copied project's autoloader. The directory name comes from the
   feature filename and scenario title, lowercased with punctuation and spaces replaced
   by hyphens.
3. The `Background` uses `PhpUnitContext` to copy a prepared configuration from
   `configurations/` to the scenario project's `phpunit.xml`. Subsequent steps run
   PHPUnit or Infection with that project as their working directory. Repeated runs
   within a scenario reuse the same project and its caches.
4. After each PHPUnit or Infection run, the contexts capture results in memory for comparisons.
   `ScenarioStateContext` resets the shared state after the scenario. Files remain
   available for inspection until that scenario's project is recreated on the next run.

| What to inspect or change | Where to look |
| --- | --- |
| Behaviour and expected results | [features/](features/), numbered `01` through `05` |
| Suite selection, excluded tags, and context services | [behat.yml](behat.yml) |
| Scenario project creation, copied files, and source/test edits | [ScenarioProjectContext.php](features/bootstrap/ScenarioProjectContext.php) |
| PHPUnit configuration, executed-test assertions, impact recordings, and cache/dependency scenarios | [PhpUnitContext.php](features/bootstrap/PhpUnitContext.php) |
| Infection commands, repeated runs, coverage/mutation/MSI assertions, and mutant isolation | [InfectionContext.php](features/bootstrap/InfectionContext.php) |
| Shared execution histories and their reset | [ScenarioState.php](features/bootstrap/ScenarioState.php) and [ScenarioStateContext.php](features/bootstrap/ScenarioStateContext.php) |
| Parsing reports into immutable snapshots | [InfectionExecutionResult.php](features/bootstrap/InfectionExecutionResult.php) and [PhpUnitExecutionResult.php](features/bootstrap/PhpUnitExecutionResult.php) |
| Subprocess execution and failure handling | [ShellCommandRunner.php](features/bootstrap/ShellCommandRunner.php) |
| Prepared PHPUnit settings and Infection settings | [configurations/](configurations/) and [infection.json5](infection.json5) |
| Project code and its PHPUnit tests | [src/](src/) and [tests/](tests/) |
| Recording what PHPUnit actually loads and executes | [phpunit/](phpunit/), starting with [RecordExecutionExtension.php](phpunit/RecordExecutionExtension.php) |

The PHPUnit extension records loaded tests, executed tests, and effective configuration
for both direct PHPUnit runs and Infection's initial test runs. When `TEST_TOKEN` is
present, it scopes all three recording paths under `var/phpunit/mutants/<hash>/`,
preserving the initial-run evidence and keeping each mutant's recordings separate.
Infection's execution report supplies coverage and mutation results; its JSON summary
supplies MSI. The result snapshots preserve earlier evidence when later runs overwrite
the report files. See [the execution report contract](../../../doc/execution-report.md)
and [the PHPUnit recording details below](#phpunit-execution-recording).

For debugging, start inside the generated scenario project. Its `var/infection/`
contains `infection.json`, `execution.jsonl`, numbered `output-<run>.log` files, and
temporary execution files under `tmp/`. Its `var/phpunit/` contains the extension's
recordings and direct PHPUnit output in `output.log`. Add new prepared PHPUnit
configurations under `configurations/`; the active setup does not copy the fixture-root
`phpunit.xml`.

The previous script is retained as `run_legacy_tests.bash`; the runner does not invoke it.

## Current blockers

At the current pin, the default suite has 22 passing scenarios and three failures in
feature `04`: sharing recordings fails in both directions, and the unrecorded-source
scenario falls back with a different explanation. See the
[current verification results](../../../doc/phpunit-tia-problems.md).

The cold-start scenario passes. The PHPUnit-seeded scenario is tagged `@skip` and
excluded by the suite filter because Infection
replaces the project's configured cache directory with `.infection/phpunit`. It runs
both initial tests instead of reusing PHPUnit's recording to select CalculatorTest.
See [the blocker](../../../doc/phpunit-tia-problems.md#3-infection-integration-gaps).

Five development-cycle scenarios pass. Two are tagged `@skip`: an existing test starts
covering Calculator after a test-body or data-provider change. PHPUnit's explicit impact
query omits that test because its previous recording does not depend on Calculator.
See [the reproductions and PHPUnit feedback](../../../doc/phpunit-tia-problems.md#1-explicit-queries-can-omit-newly-relevant-tests).

The remaining features reproduce XML opt-outs being overridden, runtime-setting changes
being missed, the wrong lock file being tracked when XML is outside the project, changed
external fixtures being omitted, and cache write failures stopping mutation testing.
An empty-selection outcome remains a proposal under `@decision_pending`.
See [the findings and open questions](../../../doc/phpunit-tia-problems.md).

Run all blocked scenarios explicitly (they are expected to fail):

```sh
cd tests/e2e/PHPUnit_TIA
php vendor/bin/behat --xdebug --profile=blocked
```

Append a feature path or use `--name` to focus a run. Scenario projects remain under
`var/behat/scenarios/`. The external-XML reproduction also leaves temporary files under
`var/behat/external/`, outside its scenario project. Scenario outlines recreate their
project for each example; their directory names use the example title.

## PHPUnit execution recording

PHPUnit recordings and direct execution output (`output.log`) are grouped under
`var/phpunit/`. Execution-result objects preserve earlier recordings in memory
when subsequent runs overwrite these files.

The extension records `TestSuite\Loaded` identities in `var/phpunit/loaded-tests.json`,
before execution filtering and TIA selection. The "executes all tests" step compares
executed identities with this inventory. The current scenarios load the full configured
suite; explicit test paths, suite selection, or test-index pruning can narrow what is
loaded, so this event does not guarantee a project-wide inventory for those cases.
Mutant processes leave this recording untouched.

The extension also writes `var/phpunit/configuration.json`, configured through
the `configurationFilePath` parameter. It records coverage metadata requirements
and targeting, impact recording and selection options, and cache and test-run
history settings. Recorded configuration paths are relative to the scenario project;
unavailable paths are `null`, and `-` retains its meaning of standard input. The impact-data filename
is derived from PHPUnit's cache directory. Mutant processes leave this snapshot
untouched.

The PHPUnit extension and subscriber in `phpunit/` record each `Test\Prepared` event's test ID
as a JSON string on its own line in `var/phpunit/executed-tests.jsonl`. The extension's
`executedTestsFilePath` parameter in `phpunit.xml` sets this path, relative to the working directory.
The event fires after
setup succeeds, immediately before the test method is invoked; data-set names are
part of the ID. Tests skipped or failing during preparation do not appear.

Each initial PHPUnit run resets the recording, including a run that selects no
tests, and removes previous mutant recordings. Every PHPUnit execution records its
effective configuration, loaded tests, and executed tests through the same extension
and subscribers. Initial and direct runs use the configured paths unchanged:

```text
var/phpunit/configuration.json
var/phpunit/loaded-tests.json
var/phpunit/executed-tests.jsonl
```

When `TEST_TOKEN` is present, the extension extracts the mutant hash from Infection's
generated PHPUnit configuration filename and inserts `mutants/<hash>/` before each
recording's basename:

```text
var/phpunit/mutants/<hash>/configuration.json
var/phpunit/mutants/<hash>/loaded-tests.json
var/phpunit/mutants/<hash>/executed-tests.jsonl
```

`TEST_TOKEN` identifies a worker and may be reused for another mutant; the hash
keeps those executions separate. The isolation scenarios evaluate two mutants
on the same worker and check both sets of recordings.

The mutant-isolation step reads these recordings for the latest Infection run and
compares both loaded and executed test IDs with the step's table, regardless of order.
It fails on missing or empty recordings and unexpected tests. Executed IDs have
the same `Test\Prepared` semantics as the initial-run recording.
