# Inheritdoc usage [(#860)](https://github.com/infection/infection/issues/860)

### Decision

Do not use `@inheritdoc` tags or any of its variants.


### Status

Accepted


### Context

Using `@inheritdoc` was done inconsistently across the codebase so the decision of whether we use it
systematically or remove it systematically had to be done.

A number of points:

- [PHPDoc](https://docs.phpdoc.org/guides/inheritance.html) provides inheritance of the docblocks
  by default when appropriate
- Static analysers such as PHPStan or Psalm can do without at the time of writting

Also it has a very limited value.


### Consequences

`@inheritdoc` tags and its variants must be removed when submitting pull requests.
