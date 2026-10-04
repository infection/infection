# PHPUnit TIA for Infection's initial tests

Behat executes only `features/01-initial-run.feature`. It covers a project with
TIA enabled in PHPUnit XML, using dependencies recorded from executed code:

- Reuse impact data recorded by a preceding PHPUnit run.
- Record impact data on a cold Infection run and select fewer tests on the next run.

Repeated Infection runs compare executed tests, Calculator's line-to-test coverage,
generated mutations, detection statuses, and MSI. Timing and process output are not
part of that comparison.

## Run

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
The fixture pins PHPUnit PR #6919 at `5f4f80f15f1bb1907e6ba277f2cec0756fbb8046`
until a release includes TIA.

The execution flow is:

1. [run_tests.bash](run_tests.bash) selects the Infection executable through
   `TIA_INFECTION` and starts the fixture's Behat with Xdebug preserved.
   [behat.yml](behat.yml) selects the feature and wires the contexts to shared services.
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
| Behaviour and expected results | [features/01-initial-run.feature](features/01-initial-run.feature) |
| Suite selection, excluded tags, and context services | [behat.yml](behat.yml) |
| Scenario project creation and copied files | [ScenarioProjectContext.php](features/bootstrap/ScenarioProjectContext.php) |
| PHPUnit configuration, recording seed, and executed-test assertions | [PhpUnitContext.php](features/bootstrap/PhpUnitContext.php) |
| Infection commands, repeated runs, and coverage/mutation/MSI assertions | [InfectionContext.php](features/bootstrap/InfectionContext.php) |
| Shared execution histories and their reset | [ScenarioState.php](features/bootstrap/ScenarioState.php) and [ScenarioStateContext.php](features/bootstrap/ScenarioStateContext.php) |
| Parsing reports into immutable snapshots | [InfectionExecutionResult.php](features/bootstrap/InfectionExecutionResult.php) and [PhpUnitExecutionResult.php](features/bootstrap/PhpUnitExecutionResult.php) |
| Subprocess execution and failure handling | [ShellCommandRunner.php](features/bootstrap/ShellCommandRunner.php) |
| Prepared PHPUnit settings and Infection settings | [configurations/](configurations/) and [infection.json5](infection.json5) |
| Project code and its PHPUnit tests | [src/](src/) and [tests/](tests/) |
| Recording what PHPUnit actually loads and executes | [phpunit/](phpunit/), starting with [RecordExecutionExtension.php](phpunit/RecordExecutionExtension.php) |

The PHPUnit extension records loaded tests, executed tests, and effective configuration
for both direct PHPUnit runs and Infection's initial test runs. It skips mutant processes
when `TEST_TOKEN` is present, so they cannot overwrite the initial-run evidence.
Infection's execution report supplies coverage and mutation results; its JSON summary
supplies MSI. The result snapshots preserve earlier evidence when later runs overwrite
the report files. See [the execution report contract](../../../doc/execution-report.md)
and [the PHPUnit recording details below](#initial-test-execution-recording).

For debugging, start inside the generated scenario project. Its `var/infection/`
contains `infection.json`, `execution.jsonl`, numbered `output-<run>.log` files, and
temporary execution files under `tmp/`. Its `var/phpunit/` contains the extension's
recordings and direct PHPUnit output in `output.log`. Add new prepared PHPUnit
configurations under `configurations/`; the active setup does not copy the fixture-root
`phpunit.xml`.

The other feature files remain drafts and are excluded by `behat.yml`.
The previous script is retained as `run_legacy_tests.bash`; the runner does not invoke it.

## Current blocker

The cold-start scenario passes. The PHPUnit-seeded scenario is tagged `@skip` and
excluded by the suite filter because Infection
replaces the project's configured cache directory with `.infection/phpunit`. It runs
both initial tests instead of reusing PHPUnit's recording to select CalculatorTest.
See [the blocker](../../../doc/TIA-notes.md#project-configured-cache-is-not-reused).

## Initial test execution recording

PHPUnit recordings and direct execution output (`output.log`) are grouped under
`var/phpunit/`. Execution-result objects preserve earlier recordings in memory
when subsequent runs overwrite these files.

The extension records `TestSuite\Loaded` identities in `var/phpunit/initial-loaded-tests.json`,
before execution filtering and TIA selection. The "executes all tests" step compares
executed identities with this inventory. The current scenarios load the full configured
suite; explicit test paths, suite selection, or test-index pruning can narrow what is
loaded, so this event does not guarantee a project-wide inventory for those cases.
Mutant processes leave this recording untouched.

The extension also writes `var/phpunit/initial-configuration.json`, configured through
the `configurationFilePath` parameter. It records coverage metadata requirements
and targeting, impact recording and selection options, and cache and test-run
history settings. Recorded configuration paths are relative to the scenario project;
unavailable paths are `null`, and `-` retains its meaning of standard input. The impact-data filename
is derived from PHPUnit's cache directory. Mutant processes leave this snapshot
untouched.

The PHPUnit extension and subscriber in `phpunit/` record each `Test\Prepared` event's test ID
as a JSON string on its own line in `var/phpunit/initial-tests.jsonl`. The extension's
`executedTestsFilePath` parameter in `phpunit.xml` sets this path, relative to the working directory.
The event fires after
setup succeeds, immediately before the test method is invoked; data-set names are
part of the ID. Tests skipped or failing during preparation do not appear.

Each initial PHPUnit run resets the recording, including a run that selects no
tests. When `TEST_TOKEN` is present, the extension registers no subscriber and
leaves the recording untouched. Infection supplies that variable to mutant
processes, so the file records only initial-test execution during an Infection run.
