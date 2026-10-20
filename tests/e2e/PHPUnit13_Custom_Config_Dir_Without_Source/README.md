# PHPUnit 13 with a custom configuration directory and no source coverage

This scenario was first added as `PHPUnit_Custom_Config_Dir`, later renamed
`PHPUnit9_Custom_Config_Dir`. This version uses PHPUnit 13 without source
coverage configured.

The PHPUnit configuration lives in `build/phpunit.xml`. All paths in the PHPUnit
and Infection configurations are relative.

This checks that the added source coverage points to `src/`, rather than
`build/src/`.
