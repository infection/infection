FROM scratch

COPY --chmod=0755 files/home/.local/bin/infection-merge-codex-otel /usr/local/bin/infection-merge-codex-otel
COPY codex-otel.toml /usr/share/infection-sbx/codex-otel.toml
