This e2e covers the regression where PHPStan mutant processes inherited the PHP memory limit derived from the initial PHPUnit run.

The PHPUnit test prints `Memory: 16.00 MB` during the initial test run. PHPUnit's own final memory line may vary between environments, but Infection reads the first matching memory line from the process output. The PHPUnit adapter consequently adds `-d memory_limit=32M` only to its mutant commands.

`phpstan-bootstrap.php` is loaded by PHPStan. It is a no-op for the initial PHPStan run. For mutant PHPStan runs, identified by `--tmp-file=...`, it asserts the regression setup:

- the loaded `php.ini`, when there is one, does not contain PHPUnit's derived `memory_limit = 32M` cap;
- the effective PHP memory limit is still `-1`.

The expected summary proves that PHPStan can evaluate its mutants without inheriting PHPUnit's derived limit:

```text
Killed by Test Framework: 1
Killed by Static Analysis: 4
Errored: 0
Escaped: 3
```
