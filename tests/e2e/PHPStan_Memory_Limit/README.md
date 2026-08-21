This e2e covers the regression where PHPStan mutant processes inherited the memory limit derived from the initial PHPUnit run.

PHPUnit now applies that limit only to its own mutant processes. The scenario deliberately requires active Xdebug and a successful XdebugHandler restart to verify that PHPStan keeps its separate memory policy.

The test forces those preconditions in `run_tests.bash`:

- non-Xdebug drivers are skipped because PCOV and phpdbg cannot exercise the temporary `php.ini` path. If the `DRIVER` environment variable is not set, the script falls back to checking whether Xdebug is loadable with `XDEBUG_MODE=coverage`.
- Xdebug must be loadable with `XDEBUG_MODE=coverage`.
- A small restart probe verifies that `XdebugHandler` restarted PHP, kept `memory_limit=-1`, and produced a loaded php.ini.

The PHPUnit test prints `Memory: 16.00 MB` during the initial test run. PHPUnit's own final memory line may vary between environments, but Infection reads the first matching memory line from the process output. PHPUnit mutant processes therefore run with `memory_limit=32M` without changing the temporary php.ini inherited by PHPStan.

`phpstan-bootstrap.php` is loaded by PHPStan. It is a no-op for the initial PHPStan run. For mutant PHPStan runs, identified by `--tmp-file=...`, it asserts that:

- the process still uses the XdebugHandler temporary php.ini;
- the effective PHP memory limit is `-1`, proving PHPStan retains its existing override.

Mutant PHPStan runs continue to use PHP `-d memory_limit=-1`, so the expected summary remains:

```text
Killed by Test Framework: 1
Killed by Static Analysis: 4
Errored: 0
Escaped: 3
```
