# Explaining TIA selection in Infection's output

A developer needs to know whether Infection ran a subset of the initial suite or why it
fell back to running all tests. The recording time also indicates how current the
dependency information is.

PHPUnit already reports a summary, reason and timestamp. Infection's final presentation
remains undecided.

Feature [04][f04] asserts selected fallback reasons, but there is no timestamp or complete
console-output scenario. PHPUnit's [explanation verbosity](../phpunit/explanation-verbosity.md)
is a separate usability request.

[f04]: ../../../tests/e2e/PHPUnit_TIA/features/04-cache-and-dependencies.feature
