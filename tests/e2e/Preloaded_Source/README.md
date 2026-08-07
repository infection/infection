## Summary

Reproduces [#3444](https://github.com/infection/infection/issues/3444): source loaded by `auto_prepend_file` before Infection installs its include interceptor cannot be replaced by a mutant. Coverage remains correct, but every mutant escapes.
