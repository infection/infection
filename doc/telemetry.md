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
| Traces  | Supported with `console` and OTLP HTTP exporters |
| Metrics | Not yet supported                                |
| Logs    | Not yet supported                                |

Unsupported exporters (for example, a metrics or logs exporter, or a trace
exporter other than `console` or `otlp`) are rejected at startup rather than
silently producing no output. Unsupported OTLP protocols are likewise
reported at startup. Additional signals and exporters will be added in later
increments.

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
- `infection.mutation_evaluation`
- `infection.mutation_evaluation.mutation` (one per started mutant evaluation)
- `infection.mutation_evaluation.heuristic_suppression`
- `infection.mutation_evaluation.heuristic`
- `infection.mutation_evaluation.mutant_analysis`
- `infection.mutation_evaluation.mutant_materialisation`
- `infection.mutation_evaluation.mutant_evaluation`
- `infection.mutation_evaluation.process`

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
| `infection.timeouts_as_escaped`                      | Whether timed-out mutants are treated as escaped when calculating MSI values.                                             |
| `infection.initial_tests.skipped`                    | Whether the initial test run was skipped.                                                                                 |
| `infection.initial_static_analysis.skipped`          | Whether the initial static analysis run was skipped because no static analysis tool was enabled.                          |
| `infection.test_framework.name`                      | Normalised configured test framework name, for example `phpunit`.                                                         |
| `infection.test_framework.version`                   | Version reported by the configured test framework adapter.                                                                |
| `infection.static_analysis_tool.name`                | Normalised configured static analysis tool name, for example `phpstan`; only emitted when static analysis is enabled.     |
| `infection.static_analysis_tool.version`             | Version reported by the configured static analysis tool adapter; only emitted with `infection.static_analysis_tool.name`. |
| `infection.source_file.count`                        | Number of source files collected for the run.                                                                             |
| `infection.mutation.count`                           | Number of generated mutations selected for mutation evaluation.                                                           |
| `infection.mutation.suppressed.count`                | Number of generated mutations suppressed before mutant evaluation, including heuristic suppression.                       |
| `infection.mutation.evaluated.count`                 | Number of mutations evaluated.                                                                                            |
| `infection.mutation.killed_by_tests.count`           | Number of mutation results with `killed by tests` detection status.                                                       |
| `infection.mutation.killed_by_static_analysis.count` | Number of mutation results with `killed by static analysis` detection status.                                             |
| `infection.mutation.escaped.count`                   | Number of mutation results with `escaped` detection status.                                                               |
| `infection.mutation.error.count`                     | Number of mutation results with `error` detection status.                                                                 |
| `infection.mutation.timed_out.count`                 | Number of mutation results with `timed out` detection status.                                                             |
| `infection.mutation.skipped.count`                   | Number of mutation results with `skipped` detection status.                                                               |
| `infection.mutation.syntax_error.count`              | Number of mutation results with `syntax error` detection status.                                                          |
| `infection.mutation.not_covered.count`               | Number of mutation results with `not covered` detection status.                                                           |
| `infection.mutation.ignored.count`                   | Number of mutation results with `ignored` detection status.                                                               |
| `infection.msi.value`                                | Final Mutation Score Indicator percentage.                                                                                |
| `infection.covered_msi`                              | Final covered-code Mutation Score Indicator percentage.                                                                   |
| `infection.msi.threshold.value`                      | Effective minimum MSI threshold, or `0.0` when no threshold is configured.                                                |
| `infection.covered_msi.threshold`                    | Effective minimum covered-code MSI threshold, or `0.0` when no threshold is configured.                                   |


## Configuration

`INFECTION_TELEMETRY=true` is required before Infection creates any telemetry
service. Once enabled, Infection reads the standard OpenTelemetry environment
variables. The most relevant at present are:

| Variable                             | Purpose                                                                    | Default                  |
|--------------------------------------|----------------------------------------------------------------------------|--------------------------|
| `INFECTION_TELEMETRY`                | Enables Infection telemetry when set to `true`.                            | unset (telemetry is off) |
| `OTEL_TRACES_EXPORTER`               | Trace exporter to use. Accepted values are `console`, `otlp`, and `none`.  | `console`                |
| `OTEL_EXPORTER_OTLP_ENDPOINT`        | Base OTLP endpoint.                                                        | unset                    |
| `OTEL_EXPORTER_OTLP_TRACES_ENDPOINT` | OTLP endpoint for traces.                                                  | unset                    |
| `OTEL_EXPORTER_OTLP_PROTOCOL`        | OTLP protocol. Use `http/protobuf` for HTTP export.                        | SDK default              |
| `OTEL_EXPORTER_OTLP_TRACES_PROTOCOL` | OTLP traces protocol. Use `http/protobuf` for HTTP export.                 | SDK default              |
| `OTEL_SERVICE_NAME`                  | Service name attached to all spans.                                        | `infection`              |
| `OTEL_SDK_DISABLED`                  | When `true`, forces the SDK off regardless of any other `OTEL_*` variable. | `false`                  |

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
