# Invalidating recordings when PHP runtime settings change

Infection allows developers to configure the PHP interpreter for the initial test run through
`--initial-tests-php-options`. Changing an INI setting can change test execution even when
the source, tests, and PHPUnit XML stay unchanged. Reusing TIA data needs to account for
runtime settings that can affect which code the tests execute.

The [runtime-setting scenario][runtime] creates a recording, then runs Infection again
with `--initial-tests-php-options="-d precision=15"`. PHPUnit continues selecting only
`CalculatorTest`, instead of invalidating the recording and running the full suite.
The scenario fails and is tagged `@skip`. It demonstrates missing invalidation for this
setting, but does not demonstrate incorrect test results.

PHPUnit's runtime fingerprint comes from [`ExecutionSettings`][settings]. It includes
PHP values configured through `<php>` in XML, but does not include every effective INI
or environment value. Infection's interpreter option does not change that XML configuration.

## Feedback for PHPUnit

Should PHPUnit track additional effective runtime settings, or should callers such as
Infection supply the runtime inputs that need to invalidate the recording?

[runtime]: ../../../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature#L79
[settings]: https://github.com/sebastianbergmann/phpunit/blob/e21b72d4ac3a9d9351eaa065638dd5e6259879bd/src/Runner/TestImpactAnalysis/ExecutionSettings.php
