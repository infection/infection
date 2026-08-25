# Initial static-analysis execution logger discrepancy

`InitialStaticAnalysisExecutionLoggerSubscriber` does not currently behave in the same way
as `InitialTestsExecutionLoggerSubscriber` when debug output or the progress bar is disabled.
This difference should be resolved separately from the test-framework adapter API work.

The two paths are intended to be equivalent where their execution phases have the same
requirements:

- `InitialTestsExecutionLoggerSubscriber` receives the initial-test events and delegates to
  `InitialTestsExecutionLogger`.
- `InitialStaticAnalysisExecutionLoggerSubscriber` receives the initial static-analysis
  events and delegates to `InitialStaticAnalysisExecutionLogger`.
- Their factories both choose between `ConsoleProgressBarLogger` and
  `ConsoleNoProgressLogger` from the global `noProgress` and `isDebugEnabled` configuration
  values.

Despite the similar wiring, the observable static-analysis output differs from the initial
test output for at least the debug and skipped-progress-bar cases. Before changing the
implementation, compare the complete event sequence and console output for both phases.
In particular, verify all four combinations of:

- progress bar enabled or disabled;
- debug output enabled or disabled.

The eventual fix should establish and test one explicit contract for:

- whether the initial process output is printed when debug mode is enabled;
- whether any progress-bar output is produced when progress is disabled;
- line breaks and phase headings when the no-progress logger is selected;
- whether the static-analysis subscriber needs any deliberate behavior that differs from
  the initial-test subscriber.

Keep the framework name and version supplied by each started event. Logger construction
must remain independent of the `TestFramework` service so resolving subscribers does not
eagerly construct the combined test framework.
