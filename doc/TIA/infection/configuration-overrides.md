# Resolving PHPUnit configuration overrides

A developer may supply `--configuration`, `-c`, or `--no-configuration` through PHPUnit
extra arguments. These can bypass the XML Infection generated for its initial test run.

The TIA integration temporarily rejects these options. Configuration selection needs a
separate fix; the old duplicate-configuration workaround is no longer needed.

There is no Behat scenario for these overrides.
