## Summary

Infection should not fail if a project has no tests at all.

Case: when the project is being bootstrapped, it can have 0 tests.

PHPUnit returns 0 exit code, saying "No tests executed!".

Infection should not fail in this case as well, so that CI passes.
