# How to contribute

Contributions are always welcome. Here are a few guidelines to be aware of:
 
 - Include unit or e2e tests for new behaviours introduced by PRs.
 - Fixed bugs MUST be covered by test(s) to avoid regression.
 - If you are on Unix-like system, run `./setup_environment.sh` to set up `pre-push` git hook.
 - All code must follow the `PSR-2` coding standard. Please see [PSR-2](https://www.php-fig.org/psr/psr-2/) for more details. 
To make this as easy as possible, we use PHPCSFixer running two simple make commands: `make cs-check` and `make cs-fix`.
 - Before implementing a new big feature, consider creating a new Issue on Github. It will save your time when the core team is not going to accept it or has good recommendations about how to proceed.
 
## Tests

The following commands can be ran to test on your local environment

 - `./tests/e2e_tests` for end to end tests
 - `bin/infection` to run infection on itself.
 - `make analyze` to run PHPCSFixer and PHPStan.

We also provide a way to run these commands in different environments, e.g. different php versions and debuggers.

 - `make test` will run all types of tests, on all environments.
 - `make test-unit` will run all unit tests on different environments
 - `make test-infection-phpdbg` will run infection with `phpdbg` against different php versions
 - `make test-e2e-phpdbg` will run e2e tests with `phpdbg` against different php versions
 - `make test-infection-xdebug` will run infection with `xdebug` against different php versions
 - `make test-e2e-xdebug` will run e2e tests with `xdebug` against different php versions
 - For all test commands, except `make test` you can add `-70`, `-71`, or `-72` to run it agains php 7.0, 7.1 or 7.2
 So to run infection wtih phpdbg on php 7.1 you would run `make test-infection-phpdbg-71`

To use these command you need to have [Docker](https://www.docker.com/get-docker) installed.
