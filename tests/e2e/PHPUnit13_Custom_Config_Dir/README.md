# PHPUnit 13 with a custom configuration directory

This scenario was first added as `PHPUnit_Custom_Config_Dir`, later renamed
`PHPUnit9_Custom_Config_Dir`. This version runs the same scenario with PHPUnit 13.

The PHPUnit configuration lives in `build/phpunit.xml`. All paths in the PHPUnit
and Infection configurations are relative.

This checks that Infection resolves each path against the configuration that
contains it and preserves PHPUnit's configured source coverage.
