#!/usr/bin/env bash

set -euo pipefail

gh stack --version

test -r /usr/share/infection-sbx/skills/gh-stack/SKILL.md
