# PHPUnit TIA for Infection's initial tests

This scenario runs **Infection**, including mutation generation and evaluation, against
PHPUnit's Test Impact Analysis PR. It checks cold/warm runs for observed-execution and
declared-target dependencies, then a run with TIA disabled. Each cold run executes two tests;
each warm run selects only the test affected by `src/Calculator.php`. Disabling TIA restores
two initial tests. Every run generates the same Plus mutant and kills it with the same test.
The verifier checks initial test identities, identical line-to-test coverage, the mutant's
assertion failure, and the absence of TIA output from mutant execution.

## Run

Use PHP 8.4.1+ with Xdebug or PCOV, Composer, and Infection's installed dependencies:

```sh
composer install --working-dir=tests/e2e/PHPUnit_TIA
./tests/e2e_tests bin/infection '^./PHPUnit_TIA$'
```

For a direct rerun from the repository root:

```sh
bash tests/e2e/PHPUnit_TIA/run_tests.bash bin/infection
```

The scenario pins PHPUnit PR #6919 at `5f4f80f15f1bb1907e6ba277f2cec0756fbb8046`.
The development dependency is temporary until a release includes TIA. The runner skips PHP
versions below 8.4.1, following other PHPUnit 13 scenarios. Each invocation clears this
scenario's `var/` and `.infection/` directories, then retains cold/warm reports and console output for inspection.

## Scope of the proof of concept

`phpunit.xml` supplies source scope, test suites, and mandatory coverage metadata. It does
not enable TIA. Infection enables recording and history in the generated initial XML and
keeps a private persistent cache in `.infection/phpunit`. Positional source selection supplies
the impact query automatically; the script supplies no impact list or configuration override.

Observed execution is the default. Declared-target runs pass
`--derive-test-impact-data-from-coverage-targets` through `--test-framework-extra-args`.
The final run passes `--do-not-record-test-impact-data` to disable TIA. Mutant commands and
configurations exclude TIA settings under either strategy.

The scenario also checks reuse of an observed recording in both directions between plain
PHPUnit and Infection, using the same absolute cache directory. The project-seeded Infection
run selects one test and preserves coverage and mutation results. Plain PHPUnit selects one
test using either the original project XML or Infection's generated XML moved to another
directory inside the project, with a different colour setting. Both show the recording
timestamp. Changing an execution setting (`-d memory_limit=513M`) instead runs both tests
and reports that the configuration changed.

The recording is fresh and test code stays unchanged. The upstream changed-test freshness
issue remains a release blocker, documented as a TODO next to automatic selection.
Dependency-lock discovery for generated configurations outside the project remains
unresolved; moving XML within the project keeps the same nearest `composer.lock`.
Automatic reuse of a project-configured cache is not wired yet: the scenario explicitly
points plain PHPUnit at Infection's cache.
