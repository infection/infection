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

`ScenarioProjectContext` recreates the isolated scenario project under `var/behat/scenarios/`;
its directory name combines the feature filename and scenario title, lowercased
with punctuation and spaces replaced by hyphens.
The contexts receive the same mutable `ScenarioState`, which `ScenarioStateContext`
resets after each scenario. It holds the scenario project path and execution histories.
Its `infectionExecutionResults` history holds immutable `InfectionExecutionResult`
snapshots of each command, console output, execution events, and summary report.
The context repeats the latest snapshot's command and compares the first and latest
results through `getFirstInfectionExecutionResult()` and `getLastInfectionExecutionResult()`.
`findLastInfectionExecutionResult()` returns `null` before any execution.
Its `phpUnitExecutionResults` history holds immutable
`PhpUnitExecutionResult` snapshots for direct PHPUnit executions and Infection's
initial tests: command, output, loaded and executed test identities, and configuration.
`findLastPhpUnitExecutionResult()` returns `null` before any execution;
`getLastPhpUnitExecutionResult()` asserts that an execution has been recorded.
Snapshots retain their contents when later executions overwrite the logs.
Infection's reports (`infection.json` and `execution.jsonl`), console logs
(`output-<run>.log`), and temporary execution files (`tmp/`) live under `var/infection/`.
`InfectionContext` runs Infection and checks mutation results;
`PhpUnitContext` configures PHPUnit, seeds impact data, and checks executed tests. Commands, execution
reports, test identities, and configuration snapshots remain there for inspection.
Each scenario copies the fixture's installed dependencies and rebuilds its Composer
autoloader to load its own source files and PHPUnit extension.
Each scenario describes its PHPUnit configuration with
`Given PHPUnit is configured to record test impact data from executed code without deriving it from coverage targets`.
The step copies `configurations/tia-observed.xml` into the scenario project as
`phpunit.xml`. The configuration stays unchanged across repeated runs.
Add prepared configurations here when new scenarios require different PHPUnit settings.
Behat is installed only in this fixture. The entry point preserves Xdebug for the
PHPUnit subprocess that records impact data.

The other feature files remain drafts and are excluded by `behat.yml`.
The previous script is retained as `run_legacy_tests.bash`; the runner does not invoke it.

## Current blocker

The cold-start scenario passes. The PHPUnit-seeded scenario is tagged `@skip` and
excluded by the suite filter because Infection
replaces the project's configured cache directory with `.infection/phpunit`. It runs
both initial tests instead of reusing PHPUnit's recording to select CalculatorTest.
See [the blocker](../../../doc/TIA-notes.md#project-configured-cache-is-not-reused).

The fixture pins PHPUnit PR #6919 at `5f4f80f15f1bb1907e6ba277f2cec0756fbb8046`.
This dependency is temporary until a release includes TIA.

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
history settings. Paths are relative to `PHPUnit_TIA`; unavailable paths are
`null`, and `-` retains its meaning of standard input. The impact-data filename
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
