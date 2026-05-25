# Telemetry

Infection can emit [OpenTelemetry][opentelemetry] data about its execution,
allowing you to observe how a run progresses, where time is spent, and which
mutants are evaluated.

Telemetry is **off by default** and only activates when
`INFECTION_TELEMETRY=true` is set. Standard `OTEL_*` environment variables can
then tune the OpenTelemetry SDK configuration. Infection exposes no
telemetry-specific configuration in `infection.json5`.

## Current support

OpenTelemetry support is being delivered incrementally. The currently
supported surface is:

| Signal  | Status                                           |
|---------|--------------------------------------------------|
| Traces  | Supported with `console` and OTLP HTTP/gRPC exporters |
| Metrics | Supported with `console` and OTLP HTTP/gRPC exporters |
| Logs    | Not yet supported                                |

Unsupported exporters (for example, a logs exporter, or a trace/metrics
exporter other than `console` or `otlp`) are rejected at startup rather than
silently producing no output. Unsupported OTLP protocols are likewise
reported at startup. The OTLP gRPC transport requires the PHP `grpc` extension
to be loaded and is not available when running Infection from the PHAR.
Additional signals and exporters will be added in later increments.

## Quick start

Enable trace emission with the console exporter:

```bash
INFECTION_TELEMETRY=true OTEL_TRACES_EXPORTER=console vendor/bin/infection --quiet
```

Each recorded span is written to standard output as a structured document.
The `--quiet` flag suppresses Infection's regular console output so that only
the spans remain on stdout.

To inspect the OpenTelemetry tracer service that Infection assembles from the
current environment without running mutation testing, use:

```bash
INFECTION_TELEMETRY=true OTEL_TRACES_EXPORTER=console vendor/bin/infection debug:telemetry
```

## What is instrumented

Infection records the following lifecycle spans for every run:

- `infection.run` (root span; covers the full execution)
- `infection.initial_tests`
- `infection.initial_static_analysis`
- `infection.mutation_generation`
- `infection.mutation_analysis`
- `infection.ast_processing`
- `infection.ast_processing.file` (one per processed source file)
- `infection.ast_processing.file.parsing`
- `infection.ast_processing.file.enrichment`
- `infection.mutation_evaluation`
- `infection.mutation_evaluation.mutation` (one per started mutant evaluation)
- `infection.mutation_evaluation.mutation.heuristic_suppression`
- `infection.mutation_evaluation.mutation.heuristic`
- `infection.mutation_evaluation.mutant_analysis`
- `infection.mutation_evaluation.mutant_analysis.materialisation`
- `infection.mutation_evaluation.mutant_analysis.evaluation`
- `infection.mutation_evaluation.mutant_analysis.evaluation.process`
- `infection.reporting`
- `infection.reporting.reporter` (one per reporter run)

Infection also records metrics derived from those spans when
`OTEL_METRICS_EXPORTER` is set to `console` or `otlp`. Metric attributes are
kept intentionally low-cardinality: project name, Infection version,
distribution, thread count, run booleans, configured framework/tool names,
phase name, mutation status, MSI category, process thread, process timeout,
process exit code, and reporter name. Mutation ids, mutator names, file paths,
line numbers, event classes, project paths, configuration paths, and Git SHAs
remain trace-only.

| Metric                                           | Instrument | Unit         | Description                                                                 |
|--------------------------------------------------|------------|--------------|-----------------------------------------------------------------------------|
| `infection.run.count`                            | Counter    | `{run}`      | Completed Infection runs.                                                   |
| `infection.run.duration`                         | Histogram  | `s`          | Full run duration.                                                          |
| `infection.phase.duration`                       | Histogram  | `s`          | Top-level phase duration, split by `infection.phase.name`.                  |
| `infection.source_file.count`                    | Histogram  | `{file}`     | Source files collected or used for mutation generation.                     |
| `infection.mutated_file.count`                   | Histogram  | `{file}`     | Source files for which at least one mutation was generated.                 |
| `infection.mutation.generated.count`             | Histogram  | `{mutation}` | Generated mutations selected for mutation evaluation.                       |
| `infection.mutation.evaluated.count`             | Histogram  | `{mutation}` | Evaluated mutations.                                                        |
| `infection.mutation.suppressed.count`            | Histogram  | `{mutation}` | Generated mutations suppressed before mutant evaluation.                    |
| `infection.mutation.*.count`                     | Histogram  | `{mutation}` | Run-summary mutation count metrics by MSI/status category.                  |
| `infection.msi`                                  | Histogram  | `%`          | Final Mutation Score Indicator percentage.                                  |
| `infection.covered_msi`                          | Histogram  | `%`          | Final covered-code Mutation Score Indicator percentage.                     |
| `infection.mutation.coverage_rate`               | Histogram  | `%`          | Final mutation code coverage percentage.                                    |
| `infection.msi.threshold`                        | Histogram  | `%`          | Effective minimum MSI threshold.                                            |
| `infection.covered_msi.threshold`                | Histogram  | `%`          | Effective minimum covered-code MSI threshold.                               |
| `infection.ast.file.duration`                    | Histogram  | `s`          | AST processing duration per processed file, without file-path attributes.   |
| `infection.ast.file.parsing.duration`            | Histogram  | `s`          | AST parsing duration per processed file, without file-path attributes.      |
| `infection.ast.file.enrichment.duration`         | Histogram  | `s`          | AST enrichment duration per processed file, without file-path attributes.   |
| `infection.mutation.count`                       | Counter    | `{mutation}` | Completed mutation evaluations, split by status and MSI category.           |
| `infection.mutation.evaluation.duration`         | Histogram  | `s`          | Mutation evaluation span duration, split by status and MSI category.        |
| `infection.mutation.runtime`                     | Histogram  | `s`          | Runtime reported by the mutation execution result.                          |
| `infection.mutant.analysis.duration`             | Histogram  | `s`          | Mutant analysis duration.                                                   |
| `infection.mutant.materialisation.duration`      | Histogram  | `s`          | Mutant materialisation duration.                                            |
| `infection.mutant.evaluation.duration`           | Histogram  | `s`          | Mutant evaluation duration.                                                 |
| `infection.mutation.queue_wait.duration`         | Histogram  | `s`          | Accumulated queue wait before mutant process execution.                     |
| `infection.mutant.process.count`                 | Counter    | `{process}`  | Mutant process executions.                                                  |
| `infection.mutant.process.duration`              | Histogram  | `s`          | Mutant process execution duration.                                          |
| `infection.reporter.duration`                    | Histogram  | `s`          | Reporter execution duration, split by reporter name.                        |

## Run Attributes

The root `infection.run` span includes run identity, execution-context,
toolchain, and run-summary attributes that are useful for filtering dashboards:

| Attribute                                            | Description                                                                                                               |
|------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------|
| `infection.project.name`                             | Project label. Uses `INFECTION_PROJECT_NAME`, then root `composer.json` name, then project directory basename.            |
| `infection.project.path`                             | Resolved project directory.                                                                                               |
| `infection.config.path`                              | Infection configuration path, relative to `infection.project.path` when possible.                                         |
| `infection.version`                                  | Infection version reported by Composer metadata.                                                                          |
| `infection.distribution`                             | `source` or `phar`.                                                                                                       |
| `vcs.ref.head.revision`                              | Current `HEAD` commit SHA when the project directory is a Git checkout.                                                   |
| `infection.thread.count`                             | Resolved mutation runner thread count.                                                                                    |
| `infection.run.source_filtered`                      | Whether the run used a source filter, for example `--filter`, `--git-diff-filter`, or `--git-diff-lines`.                 |
| `infection.run.progress_enabled`                     | Whether progress output was enabled for the run after resolving CLI options and CI detection.                             |
| `infection.timeouts_as_escaped`                      | Whether timed-out mutants are treated as escaped when calculating MSI values.                                             |
| `infection.initial_tests.skipped`                    | Whether the initial test run was skipped.                                                                                 |
| `infection.initial_static_analysis.skipped`          | Whether the initial static analysis run was skipped because no static analysis tool was enabled.                          |
| `infection.test_framework.name`                      | Normalised configured test framework name, for example `phpunit`.                                                         |
| `infection.test_framework.version`                   | Version reported by the configured test framework adapter.                                                                |
| `infection.static_analysis_tool.name`                | Normalised configured static analysis tool name, for example `phpstan`; only emitted when static analysis is enabled.     |
| `infection.static_analysis_tool.version`             | Version reported by the configured static analysis tool adapter; only emitted with `infection.static_analysis_tool.name`. |
| `infection.source_file.count`                        | Number of source files collected for the run.                                                                             |
| `infection.mutated_file.count`                       | Number of source files for which at least one mutation was generated.                                                     |
| `infection.mutation.generated.count`                 | Number of generated mutations selected for mutation evaluation.                                                           |
| `infection.mutation.evaluated.count`                 | Number of mutations evaluated.                                                                                            |
| `infection.mutation.suppressed.count`                | Number of generated mutations suppressed before mutant evaluation, including heuristic suppression.                       |
| `infection.mutation.eligible.count`                  | Number of mutations eligible for MSI.                                                                                     |
| `infection.mutation.ineligible.count`                | Number of mutations that do not contribute to MSI, such as skipped and ignored mutations.                                 |
| `infection.mutation.tested_eligible.count`           | Number of mutations eligible for covered-code MSI.                                                                        |
| `infection.mutation.covered.count`                   | Number of eligible mutations contributing positively to MSI.                                                              |
| `infection.mutation.tested_not_covered.count`        | Number of tested eligible mutations contributing negatively to MSI.                                                       |
| `infection.mutation.not_covered.count`               | Number of eligible mutations contributing negatively to MSI, including mutations not covered by tests.                    |
| `infection.mutation.not_tested.count`                | Number of mutation results with `not covered` detection status.                                                           |
| `infection.mutation.killed_by_tests.count`           | Number of mutation results with `killed by tests` detection status.                                                       |
| `infection.mutation.killed_by_static_analysis.count` | Number of mutation results with `killed by static analysis` detection status.                                             |
| `infection.mutation.escaped.count`                   | Number of mutation results with `escaped` detection status.                                                               |
| `infection.mutation.error.count`                     | Number of mutation results with `error` detection status.                                                                 |
| `infection.mutation.timed_out.count`                 | Number of mutation results with `timed out` detection status.                                                             |
| `infection.mutation.skipped.count`                   | Number of mutation results with `skipped` detection status.                                                               |
| `infection.mutation.syntax_error.count`              | Number of mutation results with `syntax error` detection status.                                                          |
| `infection.mutation.ignored.count`                   | Number of mutation results with `ignored` detection status.                                                               |
| `infection.msi.value`                                | Final Mutation Score Indicator percentage.                                                                                |
| `infection.mutation.coverage_rate.value`             | Final mutation code coverage percentage.                                                                                  |
| `infection.covered_msi.value`                        | Final covered-code Mutation Score Indicator percentage.                                                                   |
| `infection.msi.threshold`                            | Effective minimum MSI threshold, or `0.0` when no threshold is configured.                                                |
| `infection.covered_msi.threshold`                    | Effective minimum covered-code MSI threshold, or `0.0` when no threshold is configured.                                   |

## Span Attributes

All spans include `infection.event.class.start` and
`infection.event.class.end`, the fully-qualified PHP class names of the
Infection events that started and ended the span. These are intended as a
debugging aid when correlating exported spans with Infection's event subscriber
flow.

File-level spans use `code.file.path` for the source file path. The value is
emitted relative to `infection.project.path`.

Mutation-level spans and their child spans include `infection.mutation.id`,
`infection.mutator.name`, `code.file.path`, `code.line.start`, and
`code.line.end`. This makes mutation child spans queryable on their own in
backends that do not support grouping or filtering by parent span attributes.
Finished `infection.mutation_evaluation.mutation` spans also include
`infection.mutation.msi.category`, which classifies the mutation's contribution
to MSI as `covered`, `not_covered`, or `ineligible`. Timeouts are classified as
`covered` unless `infection.timeouts_as_escaped` is enabled, in which case they
are classified as `not_covered`.
Finished `infection.mutation_evaluation.mutant_analysis.evaluation` spans
include `infection.mutation.queue_wait.duration`, the accumulated queue wait
duration for the mutant evaluation in seconds.
Finished `infection.mutation_evaluation.mutant_analysis.evaluation.process`
spans include `process.exit.code` when the process exposes an exit code, and
`infection.mutation.process.timed_out`, indicating whether Infection marked the
process as timed out. They also include
`infection.mutation.process.test_framework`, the configured test framework
name, and `infection.mutation.process.thread`, the worker thread assigned to
the process.

Reporter-level spans include `infection.reporter.id`, the run-local reporter
object id, and `infection.reporter.name`, the stable reporter name configured by
Infection.

## Configuration

`INFECTION_TELEMETRY=true` is required before Infection creates any telemetry
service. Once enabled, Infection reads the standard OpenTelemetry environment
variables. The most relevant at present are:

| Variable                              | Purpose                                                                    | Default                  |
|---------------------------------------|----------------------------------------------------------------------------|--------------------------|
| `INFECTION_TELEMETRY`                 | Enables Infection telemetry when set to `true`.                            | unset (telemetry is off) |
| `OTEL_TRACES_EXPORTER`                | Trace exporter to use. Accepted values are `console`, `otlp`, and `none`.  | `console`                |
| `OTEL_METRICS_EXPORTER`               | Metrics exporter to use. Accepted values are `console`, `otlp`, and `none`. | unset (metrics are off)  |
| `OTEL_EXPORTER_OTLP_ENDPOINT`         | Base OTLP endpoint.                                                        | unset                    |
| `OTEL_EXPORTER_OTLP_TRACES_ENDPOINT`  | OTLP endpoint for traces.                                                  | unset                    |
| `OTEL_EXPORTER_OTLP_METRICS_ENDPOINT` | OTLP endpoint for metrics.                                                 | unset                    |
| `OTEL_EXPORTER_OTLP_PROTOCOL`         | OTLP protocol. Accepted values are `http/protobuf` and `grpc`.             | SDK default              |
| `OTEL_EXPORTER_OTLP_TRACES_PROTOCOL`  | OTLP traces protocol. Accepted values are `http/protobuf` and `grpc`.      | SDK default              |
| `OTEL_EXPORTER_OTLP_METRICS_PROTOCOL` | OTLP metrics protocol. Accepted values are `http/protobuf` and `grpc`.     | SDK default              |
| `OTEL_SERVICE_NAME`                   | Service name attached to all telemetry.                                    | `infection`              |
| `OTEL_SDK_DISABLED`                   | When `true`, forces the SDK off regardless of any other `OTEL_*` variable. | `false`                  |

The OTLP gRPC transport requires the PHP `grpc` extension and is not available
when running Infection from the PHAR. Use `http/protobuf` with the PHAR.

For the full list of variables the OpenTelemetry SDK recognises, see the
official [environment variable reference][otel-env-vars]. Variables that
target signals, exporters, or protocols Infection does not yet support are
either ignored or rejected, depending on what they configure.

## How-to

### Use a custom service name

Override the default `infection` service name. This is useful when several
projects feed traces into the same backend and need to be distinguished:

```bash
INFECTION_TELEMETRY=true OTEL_SERVICE_NAME=my-project-infection vendor/bin/infection
```

### Save traces to a file

Until a file exporter is natively supported, redirect the console exporter's
output:

```bash
INFECTION_TELEMETRY=true OTEL_TRACES_EXPORTER=console vendor/bin/infection --quiet > traces.log
```

The `--quiet` flag is important here; without it, Infection's regular stdout
output would be interleaved with the spans in the resulting file.

### Export traces to an OTLP Collector

Use the OTLP exporter with the HTTP/protobuf transport:

```bash
INFECTION_TELEMETRY=true \
OTEL_TRACES_EXPORTER=otlp \
OTEL_EXPORTER_OTLP_TRACES_PROTOCOL=http/protobuf \
OTEL_EXPORTER_OTLP_TRACES_ENDPOINT=http://127.0.0.1:4318/v1/traces \
OTEL_EXPORTER_OTLP_TRACES_COMPRESSION=gzip \
vendor/bin/infection
```

Use the OTLP exporter with the gRPC transport by setting the protocol to
`grpc`. This requires the PHP `grpc` extension. If a gRPC transport is
requested without the extension, Infection will fail.

```bash
INFECTION_TELEMETRY=true \
OTEL_TRACES_EXPORTER=otlp \
OTEL_EXPORTER_OTLP_TRACES_PROTOCOL=grpc \
OTEL_EXPORTER_OTLP_TRACES_ENDPOINT=http://127.0.0.1:4317 \
vendor/bin/infection
```

### Export metrics to an OTLP Collector

Enable the metrics exporter explicitly. Metrics are off by default even when
traces are enabled:

```bash
INFECTION_TELEMETRY=true \
OTEL_METRICS_EXPORTER=otlp \
OTEL_EXPORTER_OTLP_METRICS_PROTOCOL=http/protobuf \
OTEL_EXPORTER_OTLP_METRICS_ENDPOINT=http://127.0.0.1:4318/v1/metrics \
vendor/bin/infection
```

Use `OTEL_EXPORTER_OTLP_METRICS_PROTOCOL=grpc` and a gRPC collector endpoint
to export metrics over gRPC. This also requires the PHP `grpc` extension and
is not available when running Infection from the PHAR.

### Capture traces from CI

A typical CI invocation that keeps the human-readable report in the build
log while archiving spans as a separate artefact:

```bash
INFECTION_TELEMETRY=true \
OTEL_TRACES_EXPORTER=console \
OTEL_SERVICE_NAME=my-project-infection-ci \
vendor/bin/infection --quiet > infection-traces.log
```

Upload `infection-traces.log` as a build artefact for later inspection.

### Force telemetry off

Even when `OTEL_*` variables are present in the environment (for example,
when injected by a CI platform), telemetry can be forced off:

```bash
INFECTION_TELEMETRY=true OTEL_SDK_DISABLED=true vendor/bin/infection
```

This takes precedence over any other `OTEL_*` configuration.

[opentelemetry]: https://opentelemetry.io/
[otel-env-vars]: https://opentelemetry.io/docs/specs/otel/configuration/sdk-environment-variables/
