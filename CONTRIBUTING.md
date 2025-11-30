# Executing tests


## End-to-end tests

Infection contains a few end-to-end tests that can be executed. Some of those are self-contained, in which
case they can be executed by PHPUnit, and others cannot.

The end-to-end tests can be found in `tests/e2e`. The can be executed with:

```shell
make test-e2e
```


## Standard E2E tests

The standard end-to-end tests are self-contained and can be executed by PHPUnit:

```shell
make test-e2e-phpunit
```

The list can be found as follows:

```shell
vendor/bin/phpunit --group=e2e --list-tests
```


## Non-standard E2E tests

Some end-to-end tests are called "non-standard" as in they have their own script. They can be executed with:

```shell
./tests/e2e_tests <infection-executable> [<e2e-test>]

# <infection-executable>: bin/infection or bin/infection.phar
# [<e2e-test>]: optional, path to a specific e2e test, e.g. tests/e2e/Adapter_Installer
```
