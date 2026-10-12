# Selecting and recording tests when coverage reports are supplied

A developer can pass `--coverage` to reuse existing reports. Infection still runs the
initial tests, but does not need to collect those reports again.

Currently, Infection omits report arguments without automatically adding `--no-coverage`.
It skips automatic TIA setup and disables history in the generated XML, while project
recording settings can remain enabled. This was checked in
[`InitialConfigBuilder`][initial-builder] and [`PhpUnitAdapter`][adapter].
There is no Behat scenario for supplied coverage.

## Proposed policy

`$skipCoverage` means reports were supplied; `$collectCoverage` is its inverse, not the TIA
strategy. Supplied reports still undergo Infection's coverage validation and do not
constitute a PHPUnit impact recording.

The recorded decision was to add `--no-coverage` automatically when reports are supplied.
Removing report destinations alone does not prevent TIA or extensions from requesting
coverage. The proposed selection policy remains unimplemented and has **no Behat scenario**:

| Strategy | Proposed behaviour with supplied coverage |
| --- | --- |
| Observed execution | Select from compatible data without refreshing observed dependencies; fall back to the eligible full suite when unusable. |
| Declared targets | Continue recording and selection subject to metadata prerequisites, without coverage collection. |
| Disabled TIA | Run ordinary initial tests without collecting coverage or silently switching strategy. Honour explicit opt-outs. |

`--no-coverage` disables observed recording but permits derivation; `--do-not-record-test-impact-data`
disables both and is not an equivalent override. Selection still needs history, cache,
compatible assumptions, and changed-input safeguards. `--skip-initial-tests` additionally
requires supplied reports and skips the run that TIA would optimise.

[initial-builder]: ../../../src/TestFramework/PhpUnit/Config/Builder/InitialConfigBuilder.php
[adapter]: ../../../src/TestFramework/PhpUnit/Adapter/PhpUnitAdapter.php
