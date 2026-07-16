# Use PHPUnit `expectException*()` API over `try-catch`

### Context

There are two common ways to test code that is expected to throw an exception:

```php
function test_something(): void
{
    // ...

    try {
        // The statement that fails

        // This assertion is required if the statement does not throw.
        $this->fail();
    } catch (Exception $e) {
        // ...
    }
}
```

Alternatively, use PHPUnit's exception expectation API:

```php
function test_something(): void
{
    // ...

    // Other expectException*() assertions may also be used.
    $this->expectException($exception);

    // The statement that fails
}
```

This decision was prompted by concerns about readability and maintainability.


### Decision

Following [Sebastian Bergmann's][sebastian-bergmann] recommendation in
[this article][phpunit-exception-best-practices], use the `expectException*()` API when only
the exception type, message, or code needs to be asserted.

When the exception object must be inspected or further assertions must be performed after
catching it, use the `ExpectsThrowables` test utility. Pass only the statement expected to throw
to `expectToThrow()`. This prevents an exception raised during test setup from causing the test
to pass accidentally. Do not write a `try-catch` block directly in a test.

```php
final class ServiceTest extends TestCase
{
    use ExpectsThrowables;

    public function test_something(): void
    {
        $previous = new RuntimeException();
        $service = $this->createService($previous);

        $exception = $this->expectToThrow(
            static fn () => $service->execute(),
        );

        $this->assertInstanceOf(DomainException::class, $exception);
        $this->assertSame($previous, $exception->getPrevious());
    }
}
```


### Status

Accepted ([#1192][1192])


[sebastian-bergmann]: https://thephp.cc/company/consultants/sebastian-bergmann
[phpunit-exception-best-practices]: https://thephp.cc/news/2016/02/questioning-phpunit-best-practices
[1192]: https://github.com/infection/infection/pull/1192
