This end-to-end test covers a regression where PHPStan mutant processes inherited the PHP memory limit derived from the initial PHPUnit run.

The PHPUnit test outputs `Memory: 16.00 MB` during the initial test run. The PHPUnit adapter therefore adds `-d memory_limit=32M` only to its mutant commands.

PHPStan loads `phpstan-bootstrap.php`, which does nothing during the initial run. During mutant runs, it asserts that:

- The loaded `php.ini`, if any, does not contain PHPUnit's derived `memory_limit = 32M` limit.
- The effective PHP memory limit remains `-1`.

The expected summary proves that PHPStan can evaluate its mutants without inheriting PHPUnit's derived limit:

```text
Killed by Test Framework: 1
Killed by Static Analysis: 4
Errored: 0
Escaped: 3
```
