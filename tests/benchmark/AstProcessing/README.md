# AST Processing Benchmark

Measures AST enrichment and mutation traversal for source files with existing coverage.

This benchmark reuses the `benchmark-source` project and generated coverage
fixtures used by the tracing benchmark. It parses each source file, runs the
enrichment traversal with coverage traces, then runs the mutation traversal with
a dummy visitor that counts entered nodes. This isolates AST processing from
mutation generation itself.

## Commands

```bash
make benchmark_ast_processing
make profile_ast_processing
```

## Sources

The benchmark depends on:

- `tests/benchmark/Tracing/benchmark-source`
- `tests/benchmark/Tracing/coverage`

These are prepared by the same Makefile targets used for `benchmark_tracing`.

## Script

The re-usable code of the benchmark is written in [`create-main.php`](create-main.php). It can be
orchestrated by [`profile.php`](profile.php), which is also the script used for the profiling.

```synospis
Options:
    --max-node-count    Maximum number of nodes entered. Use -1 for no maximum.
    --percentage        Percentage of sources to process. [0,1], defaults to 1 = 100% of the sources processed.
    --debug             To use to execute the code without actually profiling.
```
