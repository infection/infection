This file describes the process used to get real JUnit coverage reports.

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


## PHPUnit

Within Infection e2e tests there is several PHPUnit scenarios. They should all
have a Makefile with:

```
make phpunit-coverage
ls -l var/coverage
# junit.xml
# xml/
```
