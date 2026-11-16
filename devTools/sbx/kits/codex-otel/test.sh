#!/usr/bin/env bash

set -euo pipefail

# Check the installed files and home permissions.
test -x /usr/local/bin/infection-merge-codex-otel
test -r /usr/share/infection-sbx/codex-otel.toml

test "$(stat -c %u /home/agent)" = 1000
test -w /home/agent

# Prepare an existing config with unrelated settings and old telemetry settings.
test_dir="$(mktemp -d)"
trap 'rm -rf "$test_dir"' EXIT

export CODEX_CONFIG_FILE="${test_dir}/config.toml"
export CODEX_OTEL_CONFIG_FILE=/usr/share/infection-sbx/codex-otel.toml

cat > "$CODEX_CONFIG_FILE" <<'CONFIG'
model = "test-model"

[otel]
environment = "old"

[features]
shell_tool = true
CONFIG

# Check that merging preserves unrelated settings and replaces telemetry settings.
infection-merge-codex-otel

grep -Fx 'model = "test-model"' "$CODEX_CONFIG_FILE"
grep -Fx '[features]' "$CODEX_CONFIG_FILE"
grep -Fx 'shell_tool = true' "$CODEX_CONFIG_FILE"

grep -Fx 'environment = "infection"' "$CODEX_CONFIG_FILE"
! grep -Fx 'environment = "old"' "$CODEX_CONFIG_FILE"
test "$(grep -Fxc '[otel]' "$CODEX_CONFIG_FILE")" = 1

# Check that a second merge leaves the config unchanged.
cp "$CODEX_CONFIG_FILE" "${test_dir}/first.toml"
infection-merge-codex-otel

cmp "${test_dir}/first.toml" "$CODEX_CONFIG_FILE"
