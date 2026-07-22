# Git diff from a project subdirectory

Reproduces https://github.com/infection/infection/issues/3397.

The Git repository contains a PHP project in `server/`. Infection runs from that project
directory and uses `--git-diff-base=HEAD` to select a modified source file. Paths reported by
Git are relative to the repository root and must be resolved correctly from the nested
project directory.
