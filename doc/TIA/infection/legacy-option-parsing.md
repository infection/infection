# Preserving short options in legacy PHPUnit arguments

A developer passing `-d` through `--test-framework-options` expects it to reach PHPUnit
as a short option. An older manual probe forwarded it as `--d`, which PHPUnit rejected
as ambiguous.

The legacy normalisation in `ConfigurationFactory` still prefixes option names with `--`.
This is a separate parser issue. The [runtime-setting scenario][runtime] uses
`--initial-tests-php-options` instead.

There is no Behat scenario for the legacy parsing issue. The code was checked, but the
older manual probe was not repeated in the current audit.

[runtime]: ../../../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature#L79
