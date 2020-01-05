# Title

Related to https://github.com/infection/infection/issues/396

## Summary

This E2E test proves that PHPUnit tests, dependent through `@depends` annotations are not affected by using random order flag in `phpunit.xml`:

* `executionOrder="random"`
* `resolveDependencies="true"` 

NOTE: all mutators are disabled on purpose. Here we are just checking that Initial Tests step runs without any errors.
Also, mutators are disabled because PHPUnit starting from version 8.0.6 returns different exit codes in case of fatal errors.

This means that Infection previously marked such mutant as Errored, but now as Killed. And since we run Infection with both normal and lowest (`--prefer-lowest`) dependencies, tests give us different result with different PHPUnit versions. 
