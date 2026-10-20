# PHPUnit 13 with a custom configuration directory

The PHPUnit configuration lives in `build/phpunit.xml`. Its bootstrap, test suite,
and source coverage paths are relative to `build/`, while Infection's source
paths are relative to `infection.json` in the project root.

This checks that Infection resolves each path against the configuration that
contains it and preserves PHPUnit's configured source coverage.

Requires PHP 8.4.1 or newer. Temporary artefacts are written under `var/`.
