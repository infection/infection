## Summary

The goal of this test is to ensure Infection works with PHPUnit 13 below 13.3, where the
generated configuration still uses the `cacheResult` attribute.

This project test contains:

- A class `Calculator`.
- A trait `LoggerTrait`.
- A class using a trait `UserService`.
- All of are present in two directories, a version covered in
  `src/Covered` and uncovered in `src/Uncovered`.

The coverage data can be generated with `make phpunit-coverage`.
