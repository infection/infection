# Initial-test source filtering (fixed upstream)

`initial-test:run` should use the regular command's source-filter options, including no
filter by default. The old unconditional Git filter could raise `NoSourceFound` for
untracked sources.

The command now uses shared source-filter handling. The fix and its regression test
were merged upstream in [PR #3645](https://github.com/infection/infection/pull/3645)
and are included in this branch after the upstream merge. The old behaviour dates to [PR #2762][initial-command-pr]
(`1fdaed86b`), which specified that the command should use the regular command's options.

There is no TIA Behat scenario. The [command test][initial-command-test] asserts that
default execution never asks Git for a base reference.

[initial-command-pr]: https://github.com/infection/infection/pull/2762
[initial-command-test]: ../../../tests/phpunit/Command/InitialTest/InitialTestRunCommandTest.php
