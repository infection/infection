# Tracing Benchmark

Measures the performance of parsing and processing code coverage trace data.

## Commands

```bash
make benchmark_tracing
make profile_tracing
```

## Sources

This benchmark leverages the https://github.com/infection/benchmark-source
which contains sizeable coverage report artefacts. The coverage data from the
project needs to be copied with its generic paths corrected, as some reports
contain absolute paths.

This is done by `make tests/benchmark/Tracing/coverage`.


## Script

The re-usable code of the benchmark is written in [`create-main.php`](create-main.php). It can be
orchestrated by [`profile.php`](profile.php), which is also the script used for the profiling.

```synospis
Options:
    --max-trace-count   Maximum number of traces retrieved. Use -1 for no maximum.
    --percentage        Percentage of sources to process. [0,1], defaults to 1 = 100% of the sources processed.
    --debug             To use to execute the code without actually profiling.
```
