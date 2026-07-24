# PCOV source outside the working directory

This scenario covers a PHP project whose configured sources include both a directory below
the working directory and a sibling directory. PHPUnit includes both directories in its
coverage configuration.

When PCOV has no configured `pcov.directory`, Infection must not restrict instrumentation to
the current working directory: doing so silently drops coverage for the sibling source and
makes its mutations ineligible.

## Running the scenario

Run it with a PHP installation that loads PCOV:

```shell
php -m | grep -i '^pcov$'
./tests/e2e_tests bin/infection PCOV_Source_Outside_Working_Directory
```

Run it without PCOV after selecting a PHP installation that uses another coverage driver,
such as Xdebug:

```shell
! php -m | grep -i '^pcov$'
php -m | grep -i '^xdebug$'
./tests/e2e_tests bin/infection PCOV_Source_Outside_Working_Directory
```

Setting `pcov.enabled=0` is not equivalent to the second setup: the PCOV extension remains
loaded, and Infection will still detect it as the coverage driver.
