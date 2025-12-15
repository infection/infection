# How to contribute

Contributions are always welcome. Here are a few guidelines to be aware of:

- Include tests for new behaviours introduced by PRs.
- Fixed bugs MUST be covered by test(s) to avoid regression.
- If you are on Unix-like system, you may run `./setup_environment.sh` to set up `pre-push` git
  hook.
- All code must follow the project coding style standards which can be achieved by running `make cs`
- Before implementing a new big feature, consider creating a new issue on Github. It will save your
  time when the core team is not going to accept it or has good recommendations about how to
  proceed.
- Target `master` branch


## Tests

To run the tests locally, you can run `make test`. It, however, requires [Docker][docker]. For more
granular tests, you can run `make` to see the available commands.

### End-to-end tests

Infection contains a few end-to-end tests that can be executed. Some of those are self-contained, in which
case they can be executed by PHPUnit, and others cannot.

The end-to-end tests can be found in `tests/e2e`. The can be executed with:

```shell
make test-e2e
```

#### Standard E2E tests

The standard end-to-end tests are self-contained and can be executed by PHPUnit:

```shell
make test-e2e-phpunit
```

The list can be found as follows:

```shell
vendor/bin/phpunit --group=e2e --list-tests
```

#### Non-standard E2E tests

Some end-to-end tests are called "non-standard" as in they have their own script. They can be executed with:

```shell
./tests/e2e_tests <infection-executable> [<e2e-test>]

# <infection-executable>: defaults to bin/infection, or use build/infection.phar
# [<e2e-test>]: optional grep pattern to filter tests, e.g. Adapter_Installer
```


## Benchmarks

Read the [Benchmark documentation].


<br />
<hr />

« [Go back to the readme][readme] »


[Benchmark documentation]: ../doc/benchmarking.md
[docker]: https://www.docker.com/get-docker
[readme]: /README.md
