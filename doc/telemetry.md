# Telemetry

Infection can emit [OpenTelemetry][opentelemetry] data about its execution,
allowing you to observe how a run progresses, where time is spent, and which
mutants are evaluated.

Telemetry is **off by default** and only activates once tracing is requested
through the standard `OTEL_*` environment variables. Infection exposes no
telemetry-specific configuration in `infection.json5`; configuration flows
entirely through the official OpenTelemetry environment variables, as with any
other instrumented application.

## Current support

OpenTelemetry support is being delivered incrementally. The currently
supported surface is:

| Signal  | Status                                          |
| ------- | ----------------------------------------------- |
| Traces  | Supported with `console` and OTLP HTTP exporters |
| Metrics | Not yet supported                               |
| Logs    | Not yet supported                               |

Unsupported exporters (for example, a metrics or logs exporter, or a trace
exporter other than `console` or `otlp`) are rejected at startup rather than
silently producing no output. Unsupported OTLP protocols are likewise
reported at startup. Additional signals and exporters will be added in later
increments.

## Quick start

Enable trace emission with the console exporter:

```bash
OTEL_TRACES_EXPORTER=console vendor/bin/infection --quiet
```

Each recorded span is written to standard output as a structured document.
The `--quiet` flag suppresses Infection's regular console output so that only
the spans remain on stdout.

To inspect the OpenTelemetry tracer service that Infection assembles from the
current environment without running mutation testing, use:

```bash
OTEL_TRACES_EXPORTER=console vendor/bin/infection debug:telemetry
```

## What is instrumented

Infection records the following lifecycle spans for every run:

- `infection.run` (root span; covers the full execution)
- `infection.initial_tests`
- `infection.initial_static_analysis`
- `infection.mutation_generation`
- `infection.mutation_testing`
- `infection.mutation_evaluation` (one per mutant)

## Configuration

Infection reads the standard OpenTelemetry environment variables. The most
relevant at present are:

| Variable                             | Purpose                                                                    | Default                  |
|--------------------------------------|----------------------------------------------------------------------------|--------------------------|
| `OTEL_TRACES_EXPORTER`               | Trace exporter to use. Accepted values are `console`, `otlp`, and `none`.  | unset (telemetry is off) |
| `OTEL_EXPORTER_OTLP_ENDPOINT`        | Base OTLP endpoint. Setting it also enables OTLP trace export.             | unset                    |
| `OTEL_EXPORTER_OTLP_TRACES_ENDPOINT` | OTLP endpoint for traces. Setting it also enables OTLP trace export.       | unset                    |
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
OTEL_SERVICE_NAME=my-project-infection vendor/bin/infection
```

### Save traces to a file

Until a file exporter is natively supported, redirect the console exporter's
output:

```bash
OTEL_TRACES_EXPORTER=console vendor/bin/infection --quiet > traces.log
```

The `--quiet` flag is important here; without it, Infection's regular stdout
output would be interleaved with the spans in the resulting file.

### Export traces to an OTLP Collector

Use the OTLP exporter with the HTTP/protobuf transport:

```bash
OTEL_TRACES_EXPORTER=otlp \
OTEL_EXPORTER_OTLP_TRACES_PROTOCOL=http/protobuf \
OTEL_EXPORTER_OTLP_TRACES_ENDPOINT=http://127.0.0.1:4318/v1/traces \
OTEL_EXPORTER_OTLP_TRACES_COMPRESSION=gzip \
vendor/bin/infection
```

Setting `OTEL_EXPORTER_OTLP_ENDPOINT` or
`OTEL_EXPORTER_OTLP_TRACES_ENDPOINT` is also sufficient to enable OTLP tracing,
even when `OTEL_TRACES_EXPORTER` is not set.

### Capture traces from CI

A typical CI invocation that keeps the human-readable report in the build
log while archiving spans as a separate artefact:

```bash
OTEL_TRACES_EXPORTER=console \
OTEL_SERVICE_NAME=my-project-infection-ci \
vendor/bin/infection --quiet > infection-traces.log
```

Upload `infection-traces.log` as a build artefact for later inspection.

### Force telemetry off

Even when `OTEL_*` variables are present in the environment (for example,
when injected by a CI platform), telemetry can be forced off:

```bash
OTEL_SDK_DISABLED=true vendor/bin/infection
```

This takes precedence over any other `OTEL_*` configuration.

[opentelemetry]: https://opentelemetry.io/
[otel-env-vars]: https://opentelemetry.io/docs/specs/otel/configuration/sdk-environment-variables/
