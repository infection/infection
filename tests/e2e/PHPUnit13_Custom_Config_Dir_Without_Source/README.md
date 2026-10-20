# PHPUnit 13 with a custom configuration directory and no source coverage

The PHPUnit configuration lives in `build/phpunit.xml` and does not configure
source coverage. Infection must add its source directories relative to
`infection.json` in the project root, while resolving PHPUnit's bootstrap and
test suite paths relative to `build/`.

This checks that the added source coverage points to `src/`, rather than
`build/src/`.

Requires PHP 8.4.1 or newer. Temporary artefacts are written under `var/`.
