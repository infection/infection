# Tests that testFrameworkOptions in infection.json correctly works

* [Issue #673](https://github.com/infection/infection/issues/672)

## Summary
Parameter testFrameworkOptions in infection.json did nothing.

## Full Ticket
Although I have put "initialTestsPhpOptions": "-d zend_extension=xdebug.so", inside infection.json[.dist], but I still need to use --initial-tests-php-options='-d zend_extension=xdebug.so' on the command line to get it working. Without the command line --initial-tests-php-options paraeter I get the following error:

```
Code Coverage does not exist. File /Users/admin/workspace/infection/reports/infection/temp/infection/coverage-xml/index.xml is not found. Check phpunit version Infection was run with and generated config files inside /Users/adin/workspace/infection/reports/infection/temp/infection.
```

I believe same goes true for other arguments such as testFrameworkOptions as well.

I tried to dig into the issue myself and the culprit turned out to be this part:

path: src/Command/InfectionCommand.php

```php
$input->hasOption('initial-tests-php-options') ? $input->getOption('initial-tests-php-options') : $config->getInitialTestsPhpOptions()
```

$input->hasOption('initial-tests-php-options') returns true and hence getOption gets called which returns an empty string.

Also based on the documentations, initial-tests-php-options is optional but the following code (from the same file) says otherwise:

```php
->addOption(
                'initial-tests-php-options',
                null,
                InputOption::VALUE_REQUIRED,
                'Extra php options for the initial test runner. Will be ignored if --coverage option presented.',
                ''
            )
```