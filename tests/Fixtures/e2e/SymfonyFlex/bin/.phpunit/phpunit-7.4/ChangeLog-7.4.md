# Changes in PHPUnit 7.4

All notable changes of the PHPUnit 7.4 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [7.4.5] - 2018-12-03

* Fixed [#3410](https://github.com/sebastianbergmann/phpunit/issues/3410): Parent directory of `cacheResultFile` is not created when it does not exist
* Fixed [#3418](https://github.com/sebastianbergmann/phpunit/pull/3418): Conflicting placeholder replacement and argument exporting inconsistencies in `@testdox`

## [7.4.4] - 2018-11-14

### Fixed

* Fixed [#3379](https://github.com/sebastianbergmann/phpunit/issues/3379): Dependent test of skipped test has status `-1`
* Fixed [#3394](https://github.com/sebastianbergmann/phpunit/issues/3394): Process Isolation does not work when PHPUnit is used as PHAR
* Fixed [#3398](https://github.com/sebastianbergmann/phpunit/pull/3398): Bug when replacing placeholders in `@testdox` annotation using an associative array
* Fixed [#3401](https://github.com/sebastianbergmann/phpunit/pull/3401): Test re-ordering edge cases
* Fixed [#3402](https://github.com/sebastianbergmann/phpunit/pull/3402): Listening to the tests in reverse revealed evil hidden messages

## [7.4.3] - 2018-10-23

### Changed

* Use `^3.1` of `sebastian/environment` again due to [regression](https://github.com/sebastianbergmann/environment/issues/31)

## [7.4.2] - 2018-10-23

### Fixed

* Fixed [#3354](https://github.com/sebastianbergmann/phpunit/pull/3354): Regression when `columns="max"` is used

## [7.4.1] - 2018-10-18

### Fixed

* Fixed [#3321](https://github.com/sebastianbergmann/phpunit/pull/3321): Incorrect TestDox output for data provider with indexed array
* Fixed [#3353](https://github.com/sebastianbergmann/phpunit/issues/3353): Requesting less than 16 columns of output results in an error

## [7.4.0] - 2018-10-05

### Added

* Implemented [#3127](https://github.com/sebastianbergmann/phpunit/issues/3127): Emit error when mocked method is not really mocked
* Implemented [#3224](https://github.com/sebastianbergmann/phpunit/pull/3224): Ability to enforce a time limit for tests not annotated with `@small`, `@medium`, or `@large`
* Implemented [#3272](https://github.com/sebastianbergmann/phpunit/issues/3272): Ability to generate code coverage whitelist filter script for Xdebug
* Implemented [#3284](https://github.com/sebastianbergmann/phpunit/issues/3284): Ability to reorder tests based on execution time
* Implemented [#3290](https://github.com/sebastianbergmann/phpunit/issues/3290): Ability to load a PHP script before any code of PHPUnit itself is loaded

[7.4.5]: https://github.com/sebastianbergmann/phpunit/compare/7.4.4...7.4.5
[7.4.4]: https://github.com/sebastianbergmann/phpunit/compare/7.4.3...7.4.4
[7.4.3]: https://github.com/sebastianbergmann/phpunit/compare/7.4.2...7.4.3
[7.4.2]: https://github.com/sebastianbergmann/phpunit/compare/7.4.1...7.4.2
[7.4.1]: https://github.com/sebastianbergmann/phpunit/compare/7.4.0...7.4.1
[7.4.0]: https://github.com/sebastianbergmann/phpunit/compare/7.3.5...7.4.0

