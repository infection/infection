## Summary

The goal of this test is to ensure Infection works with PHPUnit 13.3+, which deprecated the
`cacheResult` attribute in favour of `recordTestRunHistory`.

Its `phpunit.xml` orders tests by `defects` and by `duration-ascending`, which PHPUnit 13.3 reports
as test runner warnings when the recording of the test run history is disabled, as the initial run
configuration does.

See:

- https://github.com/infection/infection/issues/3445
- https://github.com/infection/infection/issues/3457

This project test contains:

- A class `Calculator`.
- A trait `LoggerTrait`.
- A class using a trait `UserService`.
- All of are present in two directories, a version covered in
  `src/Covered` and uncovered in `src/Uncovered`.

The coverage data can be generated with `make phpunit-coverage`.
