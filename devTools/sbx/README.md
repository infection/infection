# PHP sbx mixin

This directory contains the [docker sandbox] setup. The v3 PHP tooling mixin
lives in `devTools/sbx/kits/php`, alongside the other kits.

Codex remains the [`docker.io/sbx/codex:latest`][sbx-codex] workload. The PHP
mixin adds the runtime and tooling needed by Infection without replacing the
workload's credentials, network rules, storage, or lifecycle hooks.

The PHP mixin builds on `debian:trixie-slim` (Debian 13), independently of
Codex. Use it with Debian 13 workloads of the same architecture, including
Docker's Debian-based shell and agent workloads. Workloads must provide the
unchanged Debian base libraries; this kit does not support arbitrary Linux
distributions. It leaves the workload's runtime user and launch command alone.
The v3 kit frontend packages only additions and changes made by the recipe.
This setup is separate from `devTools/Dockerfile`, which supports the Docker
Compose development flow.

`make sbx-kit-test` first builds a full test image from the recipe and runs
[container-structure-test][container-structure-test] on the host. This validation
tool is supplied to the sandbox by its own kit, rather than the PHP kit.
The script also builds and checks the `container-structure-test`, `sbx`,
`gh-stack`, and `codex-otel` kits.
It builds the PHP v3 mixin
with the kit frontend as `infection-sbx-php-8.4:latest`. The full image is tagged
`infection-sbx-php-8.4-test:latest`; it is used only for validation. Neither image
is loaded as an sbx template.

The Codex workload provides the GitHub CLI (`gh`). The `devTools/sbx/kits/gh-stack` kit
installs the latest [`github/gh-stack`](https://github.com/github/gh-stack) release
for the `agent` user during sandbox creation with `gh extension install`.
It also bundles the upstream gh-stack skill through `agent-skill@1`, so it is
exposed through the workload's skill discovery paths without a local install hook.

The `container-structure-test` kit installs version 1.22.1 of the CLI. The `sbx`
kit installs version 0.51.0 of the CLI and bundles the official
[Docker skills](https://github.com/docker/skills). Both CLI kits support amd64
and arm64. Skill source revisions are pinned in each kit's YAML descriptor;
rebuild the kits after changing those revisions. When updating Docker skills,
keep the bundled-skill declarations aligned with the upstream directories.
Bundled skills are optional: a skill already present in the shared store takes
precedence, and workloads without skill discovery can still use the CLIs.

Installing the `sbx` CLI inside a sandbox does not install its daemon or connect
to the host daemon. Sandbox lifecycle commands still require a reachable sbx
daemon; the kit does not enable nested sandbox creation.

## Telemetry

Codex telemetry is optional. The OpenTelemetry settings live in
`devTools/sbx/kits/codex-otel/codex-otel.toml` and are merged into the Codex user-level config
by the `devTools/sbx/kits/codex-otel` kit when the sandbox starts. The kit
packages the settings at `/usr/share/infection-sbx/codex-otel.toml`, so startup
does not depend on a workspace path. Recreate the sandbox after changing the
TOML file to apply the new settings.

The telemetry configuration assumes the OTLP HTTP collector is reachable from
the sandbox at `http://host.docker.internal:4318`. If it runs elsewhere,
update `devTools/sbx/kits/codex-otel/codex-otel.toml`.

Proxy environment variables come from the Codex workload and sbx. The PHP mixin
must not redefine them: v3 composition rejects conflicting environment values.

Note that depending on the port used or your network policies, the connection
to the host may be denied. For example, with the value above, you will need to
execute:

```shell
sbx policy allow network localhost:4318
```

## Usage

Authenticate `gh` on the host, then configure the sandbox's GitHub credential:

```shell
sbx secret set github --command 'gh auth token'
```

The host-side proxy injects the token into GitHub API requests. Approve the
kit's use of the GitHub credential when prompted on first creation.

```shell
make sbx-create
```

The Makefile creates a dedicated `docker-container` Buildx builder named
`infection-sbx` and selects it through `BUILDX_BUILDER` for kit validation and
sandbox creation. This supports the OCI exporter even when Docker's containerd
image store is disabled. It does not change your selected builder. Override
its name with `make sbx-create SBX_BUILDER=<name>`.

The Makefile discovers local kits under `devTools/sbx/kits/` by their matching
`<kit>/<kit>.yaml` descriptor. Adding a kit there includes it in creation and
validation automatically. `test.yaml` files are not treated as descriptors.
The generated project-local kit is always included.

This validates the kits, then lets sbx build the local PHP, container-structure-test, sbx, OTEL,
gh-stack, and project-local mixins and compose them with the Codex workload. Unchanged
build steps use BuildKit's cache. The current Makefile creates a test sandbox,
which you can reopen with:

```shell
sbx run --name codex-infection-test
```

The sandbox-removal line in the Makefile is currently commented out. Reusing
an existing sandbox does not apply changed kits; remove it explicitly when
you want to recreate it.

The command also creates `devTools/sbx/kits/project-local/project-local.yaml` from
`devTools/sbx/templates/project-local.yaml` when the local file is missing. The
generated file is ignored by Git and is always included as a kit.

To build and test the shared kit images (requires Docker Buildx and
container-structure-test):

```shell
make sbx-kit-test

# Force the build, no caching
make _sbx-kit-test
```

To use just the PHP kit with a neutral shell workload:

```shell
make sbx-builder
BUILDX_BUILDER=infection-sbx sbx run shell \
  --name=php-infection-test \
  --kit=./devTools/sbx/kits/php
```

Each shared kit keeps its image checks in its own `test.yaml`. The PHP checks
run against the full Debian build image; the other kits check their standalone
images. The project-local kit has no shared image contract. These checks do not replace
checking the kit in the chosen workload, where libraries and files can differ.

To create a sandbox manually from the repository root (requires sbx 0.51 or later):

```shell
# In case the following gets outdated, you can check `make sbx-create`.
make sbx-builder
BUILDX_BUILDER=infection-sbx sbx run codex \
  --name=codex-infection-test \
  --kit=./devTools/sbx/kits/php \
  --kit=./devTools/sbx/kits/container-structure-test \
  --kit=./devTools/sbx/kits/sbx \
  --kit=./devTools/sbx/kits/codex-otel \
  --kit=./devTools/sbx/kits/gh-stack \
  --kit="git+https://github.com/docker/sbx-kits-contrib#ref=f17734585e96110334a7e9ea609d08d7b8f1c99d&dir=git-ssh-sign" \
  --kit=./devTools/sbx/kits/project-local
```

All kits must use v3; v2 and v3 kits cannot compose. The `--kit` flag only
applies when the sandbox is created. Reopening an existing sandbox does not
apply changes to its kits. Since sbx 0.51, `sbx kit add` can add image content
to an all-v3 sandbox, subject to [capability restrictions]. Kits that declare
credentials, volumes, or generated lifecycle files, for example, must be
included at creation. Recreate the sandbox to apply the complete configuration.

Existing project-local specs are preserved. When migrating one from v2, remove
`name`, set `schemaVersion: "3"`, and move `setup` into the `config` of a
`com.docker.sandbox/lifecycle@1` capability. Convert other v2 sections to their
v3 capabilities as described in the [kit authoring guide].

To add user-specific sandbox customisation, edit:

```text
devTools/sbx/kits/project-local/project-local.yaml
```

Local install hooks that download packages must declare the hosts they need
under `com.docker.sandbox/network-policy@1` → `config.install.allow`. For
example, adding PHP extensions from the Sury repository requires
`packages.sury.org`. Use `set -eu` in shell hooks so failed package commands
stop the hook immediately.

You can use the `--clone` or `--name` option to further adjust your setup.

[docker sandbox]: https://www.docker.com/products/docker-sandboxes/
[sbx-codex]: https://hub.docker.com/r/sbx/codex
[container-structure-test]: https://github.com/GoogleContainerTools/container-structure-test

[kit authoring guide]: https://docs.docker.com/ai/sandboxes/customize/author/
[capability restrictions]: https://docs.docker.com/ai/sandboxes/customize/author/capabilities/#capabilities-that-require-sandbox-creation
