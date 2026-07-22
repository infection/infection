# Git diff from a project subdirectory

Reproduces https://github.com/infection/infection/issues/3397.

The Git repository contains a PHP project in `server/` and a sibling `frontend/` directory.
Infection runs from `server/`, the repository root, and `frontend/`, always with the
configuration file from `server/`. Each run uses `--git-diff-base=HEAD` to select the same
modified source file and must produce the same result. Paths reported by Git must be resolved
relative to the configured PHP project rather than the process working directory or the
repository root.
