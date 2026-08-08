# PCOV directory containing spaces

This scenario covers a project whose source directory contains spaces. When
`pcov.directory` is not configured, Infection passes the inferred source directory to the
initial test run using PHP's `-d` option.

The PHPUnit process is executed from an argument list, so the directory is passed as one raw
argument and does not require shell escaping.

The previous implementation applied `escapeshellarg()`. PHP currently removes the resulting
matching quotes when parsing `-d`, so both forms behave equivalently. This scenario records
the intended behaviour for source paths containing spaces.

Run the scenario with a PHP installation that loads PCOV and leaves `pcov.directory` unset:

```shell
php -r 'var_dump(extension_loaded("pcov"), ini_get("pcov.directory"));'
./tests/e2e_tests bin/infection PCOV_Directory_With_Spaces
```
