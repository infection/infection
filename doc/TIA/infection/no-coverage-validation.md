# Handling --no-coverage outside the TIA integration

A developer can pass PHPUnit's longstanding `--no-coverage` option through Infection's
extra arguments. Without supplied reports, Infection currently rejects it before
reaching the TIA version gate.

Move validation to general extra-argument handling and file the pre-existing bug separately.
The option predates TIA ([PHPUnit 5.7.27][old-no-coverage]). There is no Behat scenario.

The TIA policy for [supplied coverage](supplied-coverage.md) is a separate integration task.

[old-no-coverage]: https://github.com/sebastianbergmann/phpunit/blob/5.7.27/src/TextUI/Command.php
