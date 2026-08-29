This e2e covers the regression where PHPStan mutant processes inherited the PHP memory limit derived from the initial PHPUnit run.

The scenario deliberately requires active Xdebug and a successful XdebugHandler restart so it can verify that the shared temporary `php.ini` remains untouched.

The test forces those preconditions in `run_tests.bash`:

- non-Xdebug drivers are skipped because PCOV and phpdbg cannot exercise the temporary `php.ini` path. If the `DRIVER` environment variable is not set, the script falls back to checking whether Xdebug is loadable with `XDEBUG_MODE=coverage`.
- Xdebug must be loadable with `XDEBUG_MODE=coverage`.
- A small restart probe verifies that `XdebugHandler` restarted PHP, kept `memory_limit=-1`, and produced a loaded php.ini.

The PHPUnit test prints `Memory: 16.00 MB` during the initial test run. PHPUnit's own final memory line may vary between environments, but Infection reads the first matching memory line from the process output. The PHPUnit adapter consequently adds `-d memory_limit=32M` only to its mutant commands.

`phpstan-bootstrap.php` is loaded by PHPStan. It is a no-op for the initial PHPStan run. For mutant PHPStan runs, identified by `--tmp-file=...`, it asserts the regression setup:

- the temporary `php.ini` does not contain PHPUnit's derived `memory_limit = 32M` cap;
- the effective PHP memory limit is still `-1`.

The expected summary proves that PHPStan can evaluate its mutants without inheriting PHPUnit's derived limit:

```text
Killed by Test Framework: 1
Killed by Static Analysis: 4
Errored: 0
Escaped: 3
```
