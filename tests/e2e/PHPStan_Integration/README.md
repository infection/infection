This e2e ensures Infection works correctly with the native PHPStan integration.

It also covers the regression where PHPStan mutant processes inherited the PHP memory limit derived from the initial PHPUnit run. That memory limit is written by `MemoryLimiter` into the temporary php.ini created by Composer's XdebugHandler, so the scenario deliberately requires active Xdebug and a successful XdebugHandler restart.

The test forces those preconditions in `run_tests.bash`:

- non-Xdebug drivers are skipped because PCOV and phpdbg cannot exercise the temporary `php.ini` path.
- Xdebug must be loadable with `XDEBUG_MODE=coverage`.
- A small restart probe verifies that `XdebugHandler` restarted PHP, kept `memory_limit=-1`, and produced a temporary php.ini inherited by child PHP processes.

The PHPUnit test prints `Memory: 16.00 MB` during the initial test run. PHPUnit's own final memory line may vary between environments, but Infection reads the first matching memory line from the process output. This makes `MemoryLimiter` append `memory_limit = 32M` to the temporary php.ini after the initial static analysis run and before mutant execution.

`phpstan-bootstrap.php` is loaded by PHPStan. It is a no-op for the initial PHPStan run. For mutant PHPStan runs, identified by `--tmp-file=...`, it asserts the regression setup:

- the temporary `php.ini` contains the expected `memory_limit = 32M` cap, proving `MemoryLimiter` ran;
- the effective PHP memory limit is still `-1`.

The current expected output documents the existing bug: mutant PHPStan runs see the effective `32M` limit, fail the bootstrap check, and are reported as errors:

```text
Killed by Test Framework: 1
Killed by Static Analysis: 0
Errored: 7
Escaped: 0
```

When the bug is fixed, mutant PHPStan runs should inherit the capped php.ini but override it with PHP `-d memory_limit=-1`. At that point, this fixture's expected summary should be changed to:

```text
Killed by Test Framework: 1
Killed by Static Analysis: 4
Errored: 0
Escaped: 3
```
