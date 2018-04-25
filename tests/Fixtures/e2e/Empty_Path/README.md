# Title

Fixes https://github.com/infection/infection/issues/295

## Summary

If PATH/Path is not set, fall back to `vendor/bin` in ComposerExecutableFinder
and continue Infection execution instead of displaying a weird error
