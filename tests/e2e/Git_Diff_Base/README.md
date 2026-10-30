# Git diff base references

This PHPUnit 12 project runs Infection with `--git-diff-lines` in an isolated
Git repository with two Git base references:

- `HEAD`: after modifying a committed source file, Infection must select its changed lines
  and produce one mutant killed by the tests.
- `missing-base`: Infection must fail and display Git's diagnostic and the
  failed process's exit code.
