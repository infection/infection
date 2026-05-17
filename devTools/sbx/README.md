# PHP sbx image

This directory contains a dedicated Docker image for [docker sandbox] with PHP.

It extends [`docker/sandbox-templates:codex-docker`][docker-sandbox-templates-codex] and adds the PHP runtime/tooling needed by this repository. It is intentionally separate from `devTools/Dockerfile` as the usage is different.

It uses [container-structure-test][container-structure-test] for testing the image.

## Telemetry

Codex telemetry is optional. The OpenTelemetry settings live in
`devTools/sbx/codex-otel.toml` and are merged into the Codex user-level config
by the `devTools/sbx/codex-otel-kit` kit when the sandbox starts.

The provided template assumes the OTLP HTTP collector is reachable from
the sandbox at `http://host.docker.internal:4318`. If it runs elsewhere,
update `devTools/sbx/codex-otel.toml`.

Note that depending on the port used or your network policies, the connection
to the host may be denied. For example, with the value above, you will need to
execute:

```shell
sbx policy allow network localhost:4318
```

## Usage

Build the image:

```shell
make sbx-image-build

# Force the build, no caching
make _sbx-image-build
```

Run a sandbox with the loaded template (from the repo root):

```shell
sbx run codex --template=infection-sbx-php-8.4:latest --kit=./devTools/sbx/codex-otel-kit
```

The `--kit` flag only applies when the sandbox is created. For an existing
sandbox, recreate it or apply the kit explicitly with `sbx kit add`.

[docker sandbox]: https://www.docker.com/products/docker-sandboxes/
[docker-sandbox-templates-codex]: https://hub.docker.com/layers/docker/sandbox-templates/codex-docker/images
[container-structure-test]: https://github.com/GoogleContainerTools/container-structure-test
