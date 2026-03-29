# Mutation Generation Benchmark

Measures the performance of generating mutations from source files.

## Commands

```bash
make benchmark_mutation_generator
make profile_mutation_generator
```


## Sources

The sources are saved as `sources.tar.gz` archive and are automatically untarred by
the `Makefile` when executing the mutation generation profile or benchmark.

To save changes done to the source files, execute the following command from
the root of the project:

```shell
# untar the sources
make tests/benchmark/MutationGenerator/sources

# do your changes

# tar your changes
tar --cd=tests/benchmark/MutationGenerator -czf tests/benchmark/MutationGenerator/sources.tar.gz sources

# On macOS, remove extended attributes to prevent tar warnings in CI:
cd tests/benchmark/MutationGenerator
xattr -cr sources/
COPYFILE_DISABLE=1 tar -czf sources.tar.gz sources/
```


## Script

The re-usable code of the benchmark is written in [`create-main.php`](create-main.php). It can be
orchestrated by [`profile.php`](profile.php), which is also the script used for the profiling.

```synospis
Options:
    --max-mutation-count    Maximum number of mutations retrieved. Use -1 for no maximum.
    --debug                 To use to execute the code without actually profiling.
```
