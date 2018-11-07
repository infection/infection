# Do not mutate interfaces

[#547](https://github.com/infection/infection/issues/547)

## Summary

Interfaces are always uncovered, therefore it makes no sense to mutate anything on them.

That, unless we somehow know what test covers which interface, a feature is not possible yet.
