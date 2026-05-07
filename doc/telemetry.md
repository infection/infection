# Telemetry

Infection can emit [OpenTelemetry][opentelemetry] data about its execution,
allowing you to observe how a run progresses, where time is spent, and which
mutants are evaluated.

Telemetry is **off by default** and only activates once the standard `OTEL_*`
environment variables are set. Infection exposes no telemetry-specific
configuration in `infection.json5`; configuration flows entirely through the
official OpenTelemetry environment variables, as with any other instrumented
application.

## Current support

OpenTelemetry support is being rolled out incrementally. The currently
supported surface is:

| Signal  | Status                                  |
| ------- | --------------------------------------- |
| Traces  | Supported, `console` exporter only      |
| Metrics | Not yet supported                       |
| Logs    | Not yet supported                       |

Configuring an unsupported exporter (for example, a metrics or logs exporter,
or a trace exporter other than `console`) is rejected at startup rather than
silently producing no output. OTLP export and additional signals will be
added in later increments.

## Quick start

Enable trace emission with the console exporter:

```bash
OTEL_TRACES_EXPORTER=console vendor/bin/infection --quiet
```

Each recorded span is written to standard output as a structured document.
The `--quiet` flag suppresses Infection's regular console output so that only
the spans remain on stdout.

To inspect the OpenTelemetry tracer service that Infection assembles from the
current environment, without running mutation testing, use:

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
relevant today are:

| Variable               | Purpose                                                                    | Default                  |
| ---------------------- | -------------------------------------------------------------------------- | ------------------------ |
| `OTEL_TRACES_EXPORTER` | Trace exporter to use. Only `console` is currently accepted.               | unset (telemetry is off) |
| `OTEL_SERVICE_NAME`    | Service name attached to all spans.                                        | `infection`              |
| `OTEL_SDK_DISABLED`    | When `true`, forces the SDK off regardless of any other `OTEL_*` variable. | `false`                  |

For the full list of variables the OpenTelemetry SDK recognises, see the
official [environment variable reference][otel-env-vars]. Variables that
target signals or exporters Infection does not yet support are either ignored
or rejected, depending on what they configure.

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

The `--quiet` flag is important here; without it, Infection's normal stdout
output would be interleaved with the spans in the resulting file.

### Export traces to an OTLP Collector

```bash
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
OTEL_TRACES_EXPORTER=console \
OTEL_SERVICE_NAME=my-project-infection-ci \
vendor/bin/infection --quiet > infection-traces.log
```

Upload `infection-traces.log` as a build artefact for later inspection.

### Force telemetry off

Even when `OTEL_*` variables are present in the environment (for example,
injected by a CI platform), telemetry can be forced off:

```bash
OTEL_SDK_DISABLED=true vendor/bin/infection
```

This takes precedence over any other `OTEL_*` configuration.

[opentelemetry]: https://opentelemetry.io/
[otel-env-vars]: https://opentelemetry.io/docs/specs/otel/configuration/sdk-environment-variables/