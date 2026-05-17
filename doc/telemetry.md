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

## Configuration

`INFECTION_TELEMETRY=true` is required before Infection creates any telemetry
service. Once enabled, Infection reads the standard OpenTelemetry environment
variables. The most relevant at present are:

| Variable                             | Purpose                                                                    | Default                  |
|--------------------------------------|----------------------------------------------------------------------------|--------------------------|
| `INFECTION_TELEMETRY`                | Enables Infection telemetry when set to `true`.                            | unset (telemetry is off) |
| `INFECTION_PROJECT_NAME`             | Project name attached to Infection telemetry.                              | root `composer.json` name, then project directory basename |
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

## Run Attributes

The root `infection.run` span includes run identity and execution-context
attributes that are useful for filtering dashboards:

| Attribute                                     | Description                                                                 |
|-----------------------------------------------|-----------------------------------------------------------------------------|
| `infection.project.name`                      | Project label. Uses `INFECTION_PROJECT_NAME`, then root `composer.json` name, then project directory basename. |
| `infection.project.dir`                       | Resolved project directory.                                                 |
| `infection.config.path`                       | Infection configuration path, relative to `infection.project.dir` when possible. |
| `infection.version`                           | Infection version reported by Composer metadata.                            |
| `infection.distribution`                      | `source` or `phar`.                                                         |
| `infection.git.sha`                           | Current `HEAD` commit SHA when the project directory is a Git checkout.     |
| `infection.thread.count`                      | Resolved mutation runner thread count.                                      |
| `infection.initial_tests.skipped`             | Whether the initial test run was skipped.                                   |
| `infection.initial_static_analysis.skipped`   | Whether the initial static analysis run was skipped because no static analysis tool was enabled. |

## How-to

### Use a custom service name

Override the default `infection` service name. This is useful when several
projects feed traces into the same backend and need to be distinguished:

```bash
INFECTION_TELEMETRY=true OTEL_SERVICE_NAME=my-project-infection vendor/bin/infection
```

### Use a custom project name

Infection attaches a project name to telemetry as `infection.project.name`. This
attribute is always emitted. By default, it is read from the root `composer.json`
`name` value; when that is unavailable, Infection falls back to the project
directory basename. Override it with `INFECTION_PROJECT_NAME` when the Composer
package name is missing or is not the label you want to use in dashboards:

```bash
INFECTION_TELEMETRY=true INFECTION_PROJECT_NAME=my-project vendor/bin/infection
```

For dashboards, filter by both `service.name` and `infection.project.name`.
`service.name` identifies the OpenTelemetry service boundary, while
`infection.project.name` identifies the project being mutation-tested. Using both
keeps Infection telemetry separate from unrelated services and keeps several
projects using Infection distinguishable in the same backend.

The `infection.config.path` attribute is reported relative to
`infection.project.dir`, so the same project can be grouped consistently across
local and CI environments.

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


## Dashboard Suggestions

### Run Summary

Show the latest run health at the top of the dashboard:

- Run status: passed, failed, errored, or interrupted.
- Total duration from `infection.run`.
- MSI.
- Covered MSI.
- Total mutants.
- Killed mutants.
- Escaped mutants.
- Timed-out mutants.
- Errored mutants.
- Not-covered mutants.
- Ignored or skipped mutants.

This gives users a quick answer to whether the mutation testing run was useful
and whether code quality appears to have improved or regressed.

Useful charts:

- MSI over time.
- Covered MSI over time.
- Total mutants over time.
- Escaped mutants over time.
- Runtime over time.
