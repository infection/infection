# Measuring performance and assessing concurrent runs

TIA is intended to reduce the cost of the initial test run. Benchmark complete cold and
warm Infection runs on representative projects; the small Behat fixture cannot establish
the performance benefit for real suites.

Concurrent Infection runs share the cache. Concurrency work was explicitly deferred from
this integration, and there is no benchmark or concurrency scenario.

PHPUnit already locks the reading, merging and writing of impact data in
[`TestImpactDataFile`][impact-file]. Shared storage alone therefore does not demonstrate
a write bug.

[impact-file]: https://github.com/sebastianbergmann/phpunit/blob/e21b72d4ac3a9d9351eaa065638dd5e6259879bd/src/Runner/TestImpactAnalysis/TestImpactDataFile.php
