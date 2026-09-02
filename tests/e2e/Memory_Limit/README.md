# Test for heuristics to set memory limit for mutants

[PR #258](https://github.com/infection/infection/pull/258)

[Issue #247](https://github.com/infection/infection/issues/247)

## Summary

Mutant processes are only limited by the time they could take before timing out. They should also be limited by the amount of memory they consume. This could lead to all kind of nasty issues, including having OOM Killer come for unconcerned processes, especially those having unsaved data.

## Resolution

Since PHPUnit reports how much memory the initial test suite used, its adapter can enforce a limit on its own mutant processes. The limit is set to twice the known amount, because if a normal test suite used X megabytes, a PHPUnit mutant using much more indicates an error.

The PHPUnit adapter adds the limit as a PHP `-d memory_limit=...` argument to each PHPUnit mutant command. The shared `php.ini` remains untouched, so follow-up processes from other adapters are unaffected. We only apply a limit if there isn't one set.

So far this fix can only be applied to PHPUnit. Other testing suites are not reporting memory usage.
