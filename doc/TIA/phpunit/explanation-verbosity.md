# Offering a concise impact explanation

A developer investigating why Infection selected a subset of tests needs an explanation
that remains readable for a large suite.

`--explain-impacted` currently lists individual tests and dependency paths. A class-level
view with optional detail would make the explanation easier to scan. This is a usability
preference; the ordinary test run provides a separate impact summary.

## Feedback for PHPUnit

Could the explanation offer class-level output, with individual tests and dependency paths
available when requested?

No Behat scenario covers this preference. The current output was checked in
[`ExplainImpactedCommand`][explain]. Infection's own
[selection diagnostics](../infection/selection-diagnostics.md) remain to be designed.

[explain]: https://github.com/sebastianbergmann/phpunit/blob/e21b72d4ac3a9d9351eaa065638dd5e6259879bd/src/TextUI/Command/Commands/ExplainImpactedCommand.php
