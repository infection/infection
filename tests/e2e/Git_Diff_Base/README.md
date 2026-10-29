# Git diff base references

This PHPUnit 12 project runs Infection with `--git-diff-lines` in an isolated
Git repository with valid and invalid Git base references:

- `HEAD`: after modifying a committed source file, Infection must select its changed lines
  and produce one mutant killed by the tests.
- `missing-base`: Infection must fail with the comparison context and fetch advice.
  Normal and quiet output must omit the process failure dump; verbose and debug
  output must include Git's diagnostic and the failed process's exit code.
- `feature/<info>example</info>`: the diagnostic must display the reference literally.
