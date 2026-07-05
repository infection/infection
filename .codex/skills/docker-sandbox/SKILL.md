---
name: docker-sandbox
description: Maintain Infection's Docker Sandbox image and sbx template. Use when asked to add, remove, or upgrade sandbox tooling; change the PHP sandbox image; update Codex sandbox telemetry; recreate or load the codex-infection sandbox; or diagnose devTools/sbx image and test failures.
---

# Docker Sandbox

## Mental Model

- Docker Sandbox means Docker's `sbx` product: kernel-isolated development environments built from
  templates and kits. It is not a plain `docker run` container.
- Infection's sandbox lives under `devTools/sbx/` and extends
  `docker/sandbox-templates:codex-docker`.
- This image supports Codex/PHP sandbox work in this repository. It is separate from
  `devTools/Dockerfile`, which supports the normal Docker Compose development flow.
- Do not install tools into a running sandbox as the fix. Runtime installs are disposable and do not
  change the shared image.

## Workflow

1. Read `devTools/sbx/README.md` first. Treat it as the local source of truth for usage, commands,
   telemetry notes, and manual `sbx run` examples.
2. Inspect the specific implementation file before editing:
   - image/tooling: `devTools/sbx/Dockerfile`
   - image contract: `devTools/sbx/test.yaml`
   - build and load behaviour: `devTools/sbx/build-image.sh`, `devTools/sbx/load-template.sh`,
     `devTools/sbx/config.sh`
   - Codex telemetry kit: `devTools/sbx/codex-otel.toml`,
     `devTools/sbx/codex-otel-kit/spec.yaml`, and files under
     `devTools/sbx/codex-otel-kit/files/`
   - public targets: `Makefile`
   - CI behaviour: `.github/workflows/sbx-image.yaml`
3. For image tooling changes, update `devTools/sbx/Dockerfile`, then add or adjust matching
   assertions in `devTools/sbx/test.yaml`.
4. For PHP version changes, update every image-name/version reference consistently, including
   `devTools/sbx/config.sh`, tests, `Makefile`, and `devTools/sbx/README.md`.
5. Validate with the build or test command documented in `devTools/sbx/README.md`. If `sbx` is not
   available, do not claim to have loaded or recreated the sandbox; report the partial validation.

## Guardrails

- Every baked-in tool and meaningful runtime setting should have a `container-structure-test`
  assertion.
- Prefer `apt` packages in the existing install block when available.
- For downloaded binaries, use a version `ARG` when practical and follow the existing
  `CONTAINER_STRUCTURE_TEST_VERSION` style.
- Keep comments tied to why Infection needs the tool.
- `sbx` lifecycle commands require the host `sbx` CLI. Docker image builds/tests may work anywhere
  Docker and `container-structure-test` are available.
- `sbx` kits apply at sandbox creation. Recreate the sandbox or add the kit explicitly when kit
  behaviour changes.

## Resources

- Local usage: `devTools/sbx/README.md`
- Docker Sandboxes overview: https://docs.docker.com/ai/sandboxes/
- `sbx` CLI reference: https://docs.docker.com/reference/cli/sbx/
- Custom templates and
  `sbx template load`: https://docs.docker.com/ai/sandboxes/customize/templates/
- Kits: https://docs.docker.com/ai/sandboxes/customize/kits/
- Kit spec reference: https://docs.docker.com/ai/sandboxes/customize/kit-reference/
- Container Structure Test: https://github.com/GoogleContainerTools/container-structure-test

## Don't

- Do not use `apt-get install`, `composer global require`, `npm install -g`, or similar inside a
  running sandbox as the fix.
- Do not replace the `sbx` template flow with an ad hoc `docker run`.
- Do not update `devTools/Dockerfile` for sandbox-only requirements.
