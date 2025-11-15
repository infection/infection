# Benchmarking and Profiling

Infection includes a comprehensive benchmarking and profiling system to track performance, detect
regressions and identify optimization opportunities.

## Overview

The benchmarking system provides two complementary approaches:

- **PHPBench** (Primary): Statistical benchmarking for reliable performance metrics
- **Blackfire** (Secondary): Detailed profiling for identifying performance bottlenecks

## Quick Start

```bash
# Run all benchmarks
make benchmark

# Profile with Blackfire
make profile

# Ensure the benchmark code works as expected
make test-benchmark
```

## Available Benchmarks

- Mutation Generation Benchmark: [README.md][MutationGenerationBenchmarkReadme].
- Tracing Benchmark: [README.md][TracingBenchmarkReadme].

## Using PHPBench

PHPBench provides black-box performance testing with statistical analysis. It was chosen as the
primary benchmarking tool because profilers like Blackfire introduce
significant overhead (up to 40x in nested call scenarios), distorting real performance metrics.
PHPBench provides reliable absolute timing without instrumentation overhead.

### Configuration

PHPBench is configured in [`phpbench.json`](../phpbench.json):

- **Bootstrap**: `vendor/autoload.php`
- **File pattern**: `*Bench.php`
- **Iterations**: 5 per benchmark (configurable via `#Iterations` attribute)
- **Reports**: Aggregate statistics and bar chart visualizations

### Running PHPBench benchmarks

It is recommended to use the corresponding `make benchmark_*` command to run
a specific benchmark.

### Understanding PHPBench Output

PHPBench provides several key metrics:

- `its`: the number of iterations executed. The greater, the more likely reliable the aggregated
  data is.
- `mem_peak`: the peak memory usage across all samples.
- `mode`: the [KDE mode]. The lower the
  better, simplifying it a bit, it is a better "mean" metric.
- `rstdev`:
  the [relative standard deviation]: the lower, the better. A greater value indicates the code is
  behaviour differently (e.g. one time takes 1s, another 6s – not great). A way to lower it is to
  increase the number of iterations (if the
  code is inherently unstable, for example). The more stable the code is the less iterations are
  needed. Typically, we want to stay underneath 5%.

## Using Blackfire

Blackfire provides white-box profiling with detailed call stack analysis.

### Prerequisites

1. [Install Blackfire]
2. Ensure `pcov` and `Xdebug` are not enabled

### Running Profiles

It is recommended to use the corresponding `make profile_*` command to run a
specific profile.

### Profile Scripts

Direct access to profiling scripts:

```bash
# Mutation generation profiling
php tests/benchmark/MutationGenerator/profile.php [options]

# Tracing profiling
php tests/benchmark/Tracing/profile.php [options]
```

**Options**:

- `--max-mutation-count=<int>`: Limit mutations (default: 5000, use -1 for unlimited)
- `--max-trace-count=<int>`: Limit traces (default: -1 for unlimited)
- `--debug`: Execute without profiling (for testing the script itself)

### When to Use Blackfire

Use Blackfire when you need to:

- Identify specific functions consuming the most time.
- Understand call hierarchies and patterns.
- Find unexpected bottlenecks.
- Validate optimization efforts.

**Note**: Blackfire's overhead makes absolute timing unreliable. Use it for finding hotspots, not
measuring real performance.

## Adding a New Benchmark

Follow these steps to add a new benchmark to Infection.

- Create a new directory under `tests/benchmark/` for your benchmark.
- Create `tests/benchmark/YourFeature/YourFeatureBench.php`.
- Add the `make benchmark_yourfeature` make command and add it to `make benchmark`.
- Create `tests/benchmark/YourFeature/create-main.php`.
- If you want Blackfire profiling support, create `tests/benchmark/YourFeature/profile.php`.
- Add the `make profile_yourfeature` make command and add it to `make profile`.
- Update `Infection\Tests\BenchmarkSmokeTest`.

## Further Reading

- [PHPBench Documentation]
- [Blackfire Documentation]

[Blackfire Documentation]: https://docs.blackfire.io/introduction
[Install Blackfire]: https://docs.blackfire.io/up-and-running/installation
[KDE mode]: https://en.wikipedia.org/wiki/Kernel_density_estimation.
[MutationGenerationBenchmarkReadme]: ../tests/benchmark/MutationGenerator/README.md
[relative standard deviation]: https://en.wikipedia.org/wiki/Coefficient_of_variation
[PHPBench Documentation]: https://phpbench.readthedocs.io
[TracingBenchmarkReadme]: ../tests/benchmark/Tracing/README.md
