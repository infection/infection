# Title

Related to https://github.com/infection/infection/issues/396

## Summary

This test ensures that when `phpunit.xml` contains `executeOrder="XXX"`, Infection does not try to randomize tests execution order.

Tests are implicitly dependent in `tests/SourceClassTest.php`. In order to pass, they must be run in a declaration order.
