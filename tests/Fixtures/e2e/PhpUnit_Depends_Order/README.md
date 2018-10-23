# Title

Related to https://github.com/infection/infection/issues/396

## Summary

This E2E test proves that PHPUnit tests, dependent through `@depends` annotations are not affected by using random order flag in `phpunit.xml`:

* `executionOrder="random"`
* `resolveDependencies="true"` 

