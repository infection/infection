# PHP sbx image

This directory contains a dedicated Docker image for [docker sandbox] with PHP.

It extends [`docker/sandbox-templates:codex`][docker-sandbox-templates-codex] and adds the PHP runtime/tooling needed by this repository. It is intentionally separate from `devTools/Dockerfile` as the usage is different.

It uses [container-structure-test][container-structure-test] for testing the image.

## Usage

Build the image:

```shell
make sbx-image-build
# builds an image e.g. infection-sbx-php-8.4:latest
```

Docker sandbox requires an image from a registry, so you will need to push it
first:

```shell
# Tag it under you own username
docker tag infection-sbx-php-8.4:latest <your-dockerhub-username>/infection-sbx-php-8.4:latest
docker push <your-dockerhub-username>/docker tag infection-sbx-php-8.4:latest <your-dockerhub-username>/infection-sbx-php-8.4:latest

sbx run --template=<your-dockerhub-username>/infection-sbx-php-8.4:latest codex
```

[docker sandbox]: https://www.docker.com/products/docker-sandboxes/
[docker-sandbox-templates-codex]: https://hub.docker.com/layers/docker/sandbox-templates/codex-docker/images
[container-structure-test]: https://github.com/GoogleContainerTools/container-structure-test