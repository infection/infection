# Exposing effective impact-selection options to extensions

Infection's Behat fixture uses a PHPUnit extension to record the tests loaded and executed,
and the effective configuration. The scenarios use this evidence to check whether TIA
selected tests and whether mutant processes avoided selection and recording.

Impact-selection options are absent from the merged configuration passed to extensions.
The [recording extension][extension] reparses argv through PHPUnit's CLI `Builder` to
obtain the effective selection mode and paths.

## Feedback for PHPUnit

Could the merged configuration expose the effective impact-selection mode and paths?
Extensions could then inspect the run through the configuration object used for other
options. This does not require XML equivalents for the CLI selection options.

No dedicated Behat scenario covers this API gap; the extension demonstrates the workaround.

[extension]: ../../../tests/e2e/PHPUnit_TIA/phpunit/RecordExecutionExtension.php
