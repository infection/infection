# Earlier proposal: respecting recording opt-outs in PHPUnit XML

A project may explicitly disable impact recording or test-run history in its PHPUnit XML:

```xml
<phpunit recordTestImpactData="false">
```

The earlier proposal was to honour this as an opt-out from automatic TIA, along with
`recordTestRunHistory="false"`. This proposal is superseded by the
[current design decision](../README.md#integration-direction-and-selection-policy):
Infection tries to enable TIA regardless of the project's XML settings unless the user
passes `--do-not-record-test-impact-data` through Infection's extra arguments.

[`InitialConfigBuilder`][initial-builder] intentionally overwrites both attributes with
`"true"` in the generated initial-run XML. Infection then enables automatic TIA selection.

Both examples in the [XML configuration outline][xml-opt-out] now verify this policy:
the cold run executes all tests and the warm run selects the impacted test. They are
part of the default profile. Feature [05][f05] also covers the explicit CLI recording
opt-out and the fallback when CLI arguments disable test-run history.

[initial-builder]: ../../../src/TestFramework/PhpUnit/Config/Builder/InitialConfigBuilder.php
[xml-opt-out]: ../../../tests/e2e/PHPUnit_TIA/features/05-configuration-and-boundaries.feature#L46
[f05]: ../../../tests/e2e/PHPUnit_TIA/features/05-configuration-and-boundaries.feature
