# Use `$this` instead of `self` for PHPUnit assertions

### Context

PHPUnit assertions are static methods, yet in our code base we call them with `$this` instead of
`self`.

Whilst "incorrect", this usage does not break anything. Besides:

- [PHUnit documentation][phpunit-doc] itself uses this by default
- `$this` is much more widely used than `self` in this context in the community
- all Infection code uses `$this`

There is not much shortcomings from using this other than the "incorrectness" of using a static
method as a non-static one.


### Decision

Since there is no clear benefits of adopting `self` over `$this` and given the context of its usage,
the decision is to keep the usage of `$this` over `self` in the codebase.


### Status

Accepted ([#1061][1061])


[phpunit-doc]: https://phpunit.de/manual/6.5/en/appendixes.assertions.html
[1061]: https://github.com/infection/infection/pull/1061
