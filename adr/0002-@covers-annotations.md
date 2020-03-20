# `@covers` annotations usage

### Context

PHPUnit offers a range of `@covers` annotations with the possible to enforce a strict mode or to
enforce them. The question is when should those annotations be enforced and/or if we need to enable
another settings as well?


### Decision

Since we are using the [`symfony/phpunit-bridge`][phpunit-bridge], we decide to leverage the
`Symfony\Bridge\PhpUnit\CoverageListener` in `phpunit.xml.dist` in order to avoid to require the
`@covers` annotations whilst still benefit from it.

This however does not allows to completely forgo its usage due to the following cases:

- A test testing more than one class, requiring multiple `@covers` annotations
- A test case testing a "test class", i.e. code reserved for testing purposes

For this reason, the proposal to remove the `@covers` annotations via the [PHP-CS-Fixer][php-cs-fixer]
setting `general_phpdoc_annotation_remove` has been refused.

Since no one came up with an easy or acceptable proposal to automate the process of whether an
`@covers` annotation is necessary or not, no further action has been voted for automating this 
process.


### Status

Accepted ([#1060][1060])


### Consequences

We should omit the `@covers` annotation when unnecessary

[phpunit-bridge]: https://packagist.org/packages/symfony/phpunit-bridge
[php-cs-fixer]: https://github.com/FriendsOfPHP/PHP-CS-Fixer
[1060]: https://github.com/infection/infection/pull/1060
