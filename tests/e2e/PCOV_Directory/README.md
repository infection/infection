# Provide pcov.directory if none set

Here we fool `pcov.directory` autodetection by adding empty directories PCOV will be happy to use by default. A quote:

> When `pcov.directory` is left unset, PCOV will attempt to find `src`, `lib` or, `app` in the current working directory, in that order.

https://github.com/infection/infection/issues/1497

