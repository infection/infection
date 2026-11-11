# Docker Sandbox

This directory contains Infection's [Docker Sandbox] setup. It requires sbx 0.51
or later and Docker Buildx. Run the Make commands from the repository root.

## Usage

Configure the GitHub credential used by gh-stack, then create the sandbox:

```shell
sbx secret set github --command 'gh auth token'
make sbx-create
```

`make sbx-create` runs Codex with the repository mounted as its workspace.
sbx builds the kits during creation. The Makefile prepares a Buildx builder
automatically. After changing kits, run validation and tests manually.

To use another workload or sandbox name, edit `sbx run codex` and
`--name=codex-infection-test` in the [Makefile]'s `sbx-create` recipe before
running it. Use a workload compatible with the PHP kit's Debian-based
`sbx/shell:latest` base, and omit Codex-specific kits when using another agent.

Reopen the default sandbox with:

```shell
sbx run --name codex-infection-test
```

Reopening an existing sandbox does not apply kit changes. To apply them, remove
the sandbox, then recreate it with `make sbx-create`.

## Kits

Local kits are stored under `kits/`. Each has a matching `<kit>/<kit>.yaml`
descriptor and is automatically included in creation and validation.

| Kit | Contents |
| --- | --- |
| [php](kits/php) | PHP, coverage drivers, Composer, PIE, and extension build tools. |
| [gh-stack](kits/gh-stack) | The GitHub CLI extension and its upstream skill. |
| [sbx](kits/sbx) | The sbx CLI and Docker's official skills. |
| [container-structure-test](kits/container-structure-test) | The container-structure-test CLI. |
| [codex-otel](kits/codex-otel) | Codex telemetry settings and their configuration merge helper. |

The creation command also includes the pinned upstream `git-ssh-sign` kit and
the personal `project-local` kit.

## Telemetry

The `codex-otel` kit merges [the telemetry settings](kits/codex-otel/codex-otel.toml)
into Codex's user configuration when the sandbox starts. Edit that file to change the
settings, then recreate the sandbox to apply them.

The default OTLP HTTP collector endpoint is `http://host.docker.internal:4318`.
The kit declares runtime network access to `localhost:4318`. If you change the
collector address, also update the kit's network policy.

Local kits are included automatically when you switch workloads. With Claude,
`codex-otel` still creates Codex configuration and requests collector network
access, but Claude ignores that configuration. Exclude the kit when using
another agent.

## Testing

```shell
make sbx-kit-validate  # Validate local kit descriptors.
make sbx-kit-test      # Run checks in a disposable shell sandbox.
```

Each shared kit defines its checks in `test.sh`. The test harness discovers
the kits, creates a shell sandbox, runs their checks as the agent user through
`sbx exec`, and removes the sandbox on success or failure. New shared kits must
include a test script.

Tests exclude project-local customisations, the remote signing kit, and
host-shared skills. The telemetry checks cover configuration merging; verifying
Codex startup and telemetry delivery requires a Codex sandbox. Credential
approvals must be granted in advance because the harness runs with `--no-input`.

CI validates the shared kits with Docker Buildx and the kit frontend on the
runner’s native architecture. This builds each kit without exporting an image
or requiring an sbx login. Run the sandbox integration tests separately with
`make sbx-kit-test` on a host with a working sbx daemon.

## Personal customisation

`make sbx-create` creates `kits/project-local/project-local.yaml` from
[the template](templates/project-local.yaml) if it is missing. This file is
ignored by Git and always included in your sandbox.

Use this file for personal settings, tools, and install hooks. Existing contents
are preserved. Downloads from additional hosts in install hooks may require
install-phase network rules in the kit.

[Docker Sandbox]: https://docs.docker.com/ai/sandboxes/
[Makefile]: ../../Makefile
