# Coverage fixtures information

This file describes the process used to get the coverage reports that can be
found in this directory.

> [!CAUTION]
> Note that generating coverage data is not deterministic as it contains runtime
> information which will change from an execution to another or a machine to another.
> As such, updating those reports, even if under the same settings, will result
> in tests that will need to be updated.

As part of the normalisation process, any paths that are absolute have their
path to the project replaced by `/path/to`. This can be done by executing:

```shell
cd tests/phpunit/TestFramework/Coverage/Fixtures
make
```


## Codeception

```shell
git clone https://github.com/infection/codeception-adapter
cd codeception-adapter
cd tests/e2e/Codeception_With_Suite_Overridings
make coverage
ls -l tests/_output/
# junit.xml
# coverage-xml/
```


## PhpSpec

Note that PhpSpec does not support JUnit coverage reports!

```shell
git clone https://github.com/infection/phpspec-adapter
cd phpspec-adapter
cd tests/e2e/PhpSpec
make phpspec
ls -l var/phpspec-coverage
# index.xml
# ...
```


## PHPUnit

Within Infection e2e tests there are several PHPUnit scenarios. They should all
have a Makefile with:

```shell
cd tests/e2e/PHPUnit_11
make phpunit-coverage
ls -l var/coverage
# junit.xml
# xml/
```
