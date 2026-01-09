This file describes the process used to get real JUnit coverage reports.

The JUnit coverage reports sometimes contain absolute paths. The path to the
project has been replaced by `/path/to`.


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
