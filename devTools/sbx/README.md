# PHP sbx image

This directory contains a dedicated Docker image for [docker sandbox] with PHP.

It extends [`docker/sandbox-templates:codex`][docker-sandbox-templates-codex] and adds the PHP runtime/tooling needed by this repository. It is intentionally separate from `devTools/Dockerfile` as the usage is different.

It uses [container-structure-test][container-structure-test] for testing the image.

## Usage

Build the image:

```shell
make sbx-image-build
```

This builds `infection-sbx-php-8.4:latest`, runs `container-structure-test`,
saves the image as a tar file under `var/cache/sbx`, then loads it into `sbx`
with `sbx template load`.

The build is cached by Dockerfile content and build inputs. Re-running the
command reloads the cached tar into `sbx` without rebuilding the Docker image.
To rebuild unconditionally:

```shell
make _sbx-image-build
```

Run a sandbox with the loaded template:

```shell
sbx run --template=infection-sbx-php-8.4:latest codex
```

[docker sandbox]: https://www.docker.com/products/docker-sandboxes/
[docker-sandbox-templates-codex]: https://hub.docker.com/layers/docker/sandbox-templates/codex-docker/images
[container-structure-test]: https://github.com/GoogleContainerTools/container-structure-test
