# Signature mutants of methods with attributes

* https://github.com/infection/playground/pull/39#issuecomment-6133890685

## Summary

Mutants on the signature of a method with an attribute on the line above it (e.g. `PublicVisibility`)
were reported as not covered, even though tests execute the method.

PHP-Parser's `ClassMethod::getStartLine()` includes attribute groups, whereas php-code-coverage 14.1.5+
(sebastianbergmann/php-code-coverage@ac953b45) starts the method range at the method name line. Hence
PHPUnit 13 and the PHP 8.4.1+ requirement.
