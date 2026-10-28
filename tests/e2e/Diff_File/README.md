# Diff file

## Summary

`--diff-file` reads the changed lines from a unified diff file instead of asking git. Only the mutation on a line the
diff adds is generated: `hello()` is mutated, `goodbye()` is not.
