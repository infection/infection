In this session, we are evaluating the PHPUnit Test Impact Analysis feature (https://github.com/sebastianbergmann/phpunit/pull/6919). The feature is not merged.

The goal is to integrate this feature in infection.

This branch is a proof of concept. We are pinning a specific PHPUnit version to be able to try that
feature out. Additionally, we are capturing the desired scenarios in the end-to-end test @tests/e2e/PHPUnit_TIA.

Failures and blockers are expected, we try to keep track of the feedback to report to PHPUnit in @doc/TIA/README.md.
