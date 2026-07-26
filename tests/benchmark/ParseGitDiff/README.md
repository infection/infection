# Parse GitDiff Benchmark

Measures the performance of parsing the changed lines from a git diff.

## Commands

```bash
make benchmark_parse_git_diff
make profile_parse_git_diff
```


## Sources

This benchmark uses the file `diff` which is an example of `git diff --unified=0` output.

It is generated via [`generator.php`](generator.php) which is a script that was AI generated to generate
a Generalized Pareto Distribution to generate a realistic commit size distribution.

```shell
# generate the diff
make tests/benchmark/ParseGitDiff/diff
```


## Script

The re-usable code of the benchmark is written in [`parse-diff.php`](parse-diff.php). It can be
orchestrated by [`profile.php`](profile.php), which is also the script used for the profiling. It

```synospis
Options:
    --debug     To use to execute the code without actually profiling.
```
