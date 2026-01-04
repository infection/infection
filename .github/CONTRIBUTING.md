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

### PhpParser visitor tests

To test `PhpParser` visitors check out [its dedicated documentation][visitor-documentation].


### End-to-end tests

Infection contains a few end-to-end tests that can be executed. Some of those are self-contained, in which
case they can be executed by PHPUnit, and others cannot.

The end-to-end tests can be found in `tests/e2e`. They can be executed with:

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
./tests/e2e_tests <infection-executable> [<e2e-test-name>]

# <infection-executable>: defaults to bin/infection, or use dist/infection.phar
# [<e2e-test>]: optional grep pattern to filter tests, e.g. Adapter_Installer. The
#  list of tests available can be found in tests/e2e.
```

### Anatomy of an E2E test

Each directory in `tests/e2e` is an end-to-end test scenario.

By default, it is structured as follows:

- `README.md`: a more detailed description of the scenario or of why
  this scenario exists.
- `expected-output.txt`: the expected output from the test process.
- `var/infection.log` or `infection.log` (not committed): the actual output from
  the test process. The former is the recommendation, but not all tests were updated.
- `run_test.bash` (optional): the script to use to execute the test. If none
  is provided, then the default `tests/e2e/standard_script.bash` one is used.
  Note that using a custom test script means the structure of the test may
  also change.

If  `tests/e2e/standard_script.bash` is used, then:

- The process exiting with a non `0` exit code is treated as failed. The output
  will be logged in both the console and `tests/e2e/error.log`.
- It will execute the given infection executable in the test directory and then
  use `diff` to compare the expected and actual outputs.


### How to add an E2E test

A basic helper script is available:

```shell
./tests/add_new_e2e
```


## Benchmarks

Read the [Benchmark documentation].


<br />
<hr />

« [Go back to the readme][readme] »


[Benchmark documentation]: ../doc/benchmarking.md
[docker]: https://www.docker.com/get-docker
[readme]: /README.md
[visitor-documentation]: ../tests/phpunit/PhpParser/Visitor/README.md
