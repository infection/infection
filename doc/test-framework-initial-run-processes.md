# Test framework initial-run processes

## `InitialRunProcessFactory`

`src/TestFramework/Common/InitialRunProcessFactory.php` currently creates initial-run
processes for both test frameworks and static-analysis frameworks. Its boolean argument
selects between `OriginalPhpProcess` and Symfony's regular `Process`.

Coverage-producing test-framework runs use `OriginalPhpProcess`. Infection's main process
runs with coverage extensions disabled, so `OriginalPhpProcess` temporarily restores the
original PHP configuration and makes the coverage driver available to the initial test run.

Static-analysis initial runs do not produce coverage. PHPStan and Mago therefore pass
`false` and use a regular `Process`, preserving Infection's persistent PHP configuration.

This is an asymmetry worth revisiting. The common factory keeps the process-lifecycle choice
out of each framework, but its boolean exposes a test-framework-specific coverage concern to
static-analysis implementations. A later iteration should consider whether test frameworks
should own the `OriginalPhpProcess` choice while static-analysis frameworks construct their
regular processes without this factory.
