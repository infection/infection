#!/usr/bin/env bash

set -euo pipefail

sbx --version | grep -E '^sbx version v0\.51\.0 '

# Check the bundled Docker skills.
for skill in \
    docker-agent-config \
    docker-agent-deploy \
    docker-agent-run \
    docker-build-strategies \
    docker-compose-patterns \
    docker-destructive-guardrails \
    docker-docs \
    docker-project-foundations \
    docker-sandboxes-env \
    docker-sandboxes-kits \
    docker-sandboxes-lifecycle \
    docker-sandboxes-network-credentials
do
    test -r "/usr/share/infection-sbx/skills/${skill}/SKILL.md"
done
