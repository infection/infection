The sources are saved as a tar.gz archive and are automatically untarred by
the `Makefile` when executing the mutation generation profile or benchmark.

To save changes done to the source files, execute the following command from
the root of the project:

```shell
tar -czf sources.tar.gz -C tests/benchmark/MutationGenerator sources
```
