## Summary

The goal of this test is to ensure Infection works with PHPUnit 9.3+.

See: https://github.com/infection/infection/issues/1283.

This project test contains:

- A class `Calculator`.
- A trait `LoggerTrait`.
- A class using a trait `UserService`.
- All of are present in two directories, a version covered in
  `src/Covered` and uncovered in `src/Uncovered`.

The coverage data can be generated with `make phpunit-coverage`.
