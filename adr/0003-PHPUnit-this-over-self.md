# Use `$this` instead of `self` for PHPUnit assertions

### Context

PHPUnit assertions are static methods, but this codebase calls them with `$this` instead of
`self`.

Although calling a static method through an instance may appear unconventional, it works as
expected. In addition:

- The [PHPUnit documentation][phpunit-doc] uses `$this` by default.
- `$this` is more widely used than `self` for assertions within the PHP community.
- Infection consistently uses `$this` for assertions.

The only notable drawback is the unconventional syntax of calling a static method as an
instance method.


### Decision

Continue to use `$this` instead of `self` for PHPUnit assertions. Adopting `self` offers no
clear benefit over the established convention.


### Enforcement

[PHP-CS-Fixer][php-cs-fixer] enforces this convention through the
[`php_unit_test_case_static_method_calls` rule][php-unit-test-case-static-method-calls],
configured with `call_type` set to `this`.


### Status

Accepted ([#1061][1061])


[phpunit-doc]: https://phpunit.de/manual/6.5/en/appendixes.assertions.html
[php-cs-fixer]: https://github.com/FriendsOfPHP/PHP-CS-Fixer
[php-unit-test-case-static-method-calls]: https://cs.symfony.com/doc/rules/php_unit/php_unit_test_case_static_method_calls.html
[1061]: https://github.com/infection/infection/pull/1061
