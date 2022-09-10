# Ensures Infection works with `dg/bypass-finals`

* https://github.com/dg/bypass-finals/issues/9
* https://github.com/infection/infection/issues/1275

## Summary

`dg/bypass-finals` before version 0.14.1 overridden Infection's Stream Wrapper and Infection did not create any Mutants.

This tests ensures that starting from 0.14.1 `dg/bypass-finals` works good and do not "disables" Infection's Stream Wrapper.
