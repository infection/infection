This directory contains a mini PHPUnit project for testing code coverage tracing.

It contains a traditional `src` directory for the source code and `tests` for the
tests. New source code or tests can be added any time for covering new cases.

The tests must be generated with `make test`. It takes care of creating the
coverage report and handling the "placeholder-making"*. Note that this process
is **not** deterministic as the order of the tests is random, hence the order
in which they are listed in the coverage reports (which match the execution order)
will be random as well.

*: The reports contain absolute paths which are not useful for the coverage
reporting as they are not relative to the project root. To palliate to that we
replace them with a placeholder and the test will replace the placeholders by
the actual paths.
