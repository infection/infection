# Reusing the project's configured impact cache

A developer may already have a TIA recording from a normal PHPUnit run. Infection should
reuse the recording from the project's configured cache to reduce its initial test run.

The proof of concept replaces PHPUnit's `cacheDirectory` with `.infection/phpunit`.
It therefore misses recordings stored in the project's configured cache.

The [PHPUnit-seeded scenario][project-cache] fails and is tagged `@skip`: it expects only
`CalculatorTest`, but both initial tests run.

Infection needs to honour the configured cache. This alone will not resolve PHPUnit's
[recording-sharing problem](../phpunit/recording-sharing.md), which also occurs when both
tools explicitly use the same cache directory.

[project-cache]: ../../../tests/e2e/PHPUnit_TIA/features/01-initial-run.feature#L10
