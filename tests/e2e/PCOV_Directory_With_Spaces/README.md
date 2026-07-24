# PCOV directory containing spaces

This scenario covers a project whose source directory contains spaces. When PCOV has no
configured `pcov.directory`, Infection provides the source directory to the initial test run
as a PHP `-d` argument.

Symfony Process receives the command as an argument list, so the directory must remain
unescaped. Shell quotes would become part of PCOV's configured directory and prevent it from
collecting coverage for the source file.

Run the scenario with a PHP installation that loads PCOV and leaves `pcov.directory` unset:

```shell
php -r 'var_dump(extension_loaded("pcov"), ini_get("pcov.directory"));'
./tests/e2e_tests bin/infection PCOV_Directory_With_Spaces
```
