# Pest Test Framework integration with Infection

* https://github.com/pestphp/pest/pull/291
* https://github.com/infection/infection/issues/1476

## Summary

This test ensures Pest is working correctly with Infection.

Cases:

- `ForPest.php` file is tested only by Pest
- `ForPhpUnit.php` file is tested only by PhpUnit
- `Calculator.php` file is tested by Pest and PhpUnit

To manually check it, run:

```
git clone git@github.com:infection/infection.git
cd infection
git checkout feature/pest-adapter

cd tests/e2e/PestTestFramework
composer install

XDEBUG_MODE=coverage ../../../bin/infection --test-framework=pest --log-verbosity=all -s
```
