# Respecting explicit recording opt-outs in PHPUnit XML

A project may explicitly disable impact recording or test-run history in its PHPUnit XML:

```xml
<phpunit recordTestImpactData="false">
```

Infection is expected to honour this as an opt-out from automatic TIA. The same applies
to `recordTestRunHistory="false"`, because selection requires history. An absent setting
must still allow Infection to enable the optimisation automatically.

Currently, [`InitialConfigBuilder`][initial-builder] overwrites both attributes with
`"true"` in the generated initial-run XML. Infection then enables automatic TIA selection.

Both examples in the [XML opt-out outline][xml-opt-out] fail and are tagged `@skip`.
The equivalent CLI recording and history opt-outs already work, as verified by feature [05][f05].

[initial-builder]: ../../../src/TestFramework/PhpUnit/Config/Builder/InitialConfigBuilder.php
[xml-opt-out]: ../../../tests/e2e/PHPUnit_TIA/features/05-configuration-and-boundaries.feature#L47
[f05]: ../../../tests/e2e/PHPUnit_TIA/features/05-configuration-and-boundaries.feature
