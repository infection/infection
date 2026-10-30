# Git diff base references

This PHPUnit 12 project runs Infection with `--git-diff-lines` in an isolated
Git repository with valid and invalid Git base references:

- `HEAD`: after modifying a committed source file, Infection must select its changed lines
  and produce one mutant killed by the tests.
- `missing-base`: at normal verbosity, Infection must fail with the comparison context
  and an explanation that fetching a missing base reference may help.
