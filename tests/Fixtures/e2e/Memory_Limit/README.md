# Test for heuristics to set memory limit for mutants

[PR #258](https://github.com/infection/infection/pull/258)

[Issue #247](https://github.com/infection/infection/issues/247)

## Summary

Mutant processes are only limited by the time they could take before timing out. They should also be limited by the amount of memory they consume. This could lead to all kind of nasty issues, including having OOM Killer come for unconcerned processes, especially those having unsaved data.

## Resolution

Since we know how much memory the initial test suite used, and only if we know, we can enforce a memory limit upon all mutation processes. Limit is set to be twice the known amount, because if we know that a normal test suite used X megabytes, if a mutant uses a lot more, this is a definite error.

Memory limit is introduced by altering a known temporary php.ini to include a directive to enable the limit as the very last line. We only apply a memory limit if there isn't one set.

So far this fix can only be applied to PHPUnit. Other testing suites are not reporting memory usage.
