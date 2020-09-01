# Use PHPUnit `expectException*()` API over `try-catch`

### Context

When executing code that is expected to fail in a test case, there is two ways to do this:

```php
function test_something(): void {
    // ...

    try {
        // the statement that fail
        $this->fail();
    } catch (Exception $e) {
        // ...
    }
}
```

Or:

```php
function test_something(): void {
    // ...

    $this->expectException($exception)

    // the statement that fail
}
```


### Decision

As recommended by [Sebastian Bergmann][sebastian-bergmann] in
[this article][phpunit-exception-best-practices], since in both cases a PHPUnit specific API is
necessary, the decision taken is to leverage the `expectException*()` API when possible.

A pull request to fix this practice in the whole codebase may be done but has not been made
mandatory. New pull requests though should stick to this practice.


### Status

Accepted ([#1090][1090])


[sebastian-bergmann]: https://thephp.cc/company/consultants/sebastian-bergmann
[phpunit-exception-best-practices]: https://thephp.cc/news/2016/02/questioning-phpunit-best-practices
[1090]: https://github.com/infection/infection/pull/1061
