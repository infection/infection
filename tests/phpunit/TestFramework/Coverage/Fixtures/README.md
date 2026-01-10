This file describes the process used to get real JUnit coverage reports.

> [!CAUTION]
> Note that generating coverage data is not deterministic as it contains runtime
information which will change from an execution to another or a machine to another.

As part of the normalisation process, any paths that are absolute have their
path to the project replaced by `/path/to`. This can be done by executing:

```
cd <directory containing this file>
make
```


## Codeception (`codeception-junit.xml`)

```shell
git clone github.com/infection/codeception-adapter
cd codeception-adapter
cd tests/e2e/Codeception_With_Suite_Overridings
make coverage
ls -l tests/_output/
# junit.xml
# coverage-xml/
```


## PhpSpec
``
PhpSpec does not support JUnit coverage reports!


## PHPUnit `phpunit-XX-junit.xml`)

Within Infection e2e tests there are several PHPUnit scenarios. They should all
have a Makefile with:

```
make phpunit-coverage
ls -l var/coverage
# junit.xml
# xml/
```
