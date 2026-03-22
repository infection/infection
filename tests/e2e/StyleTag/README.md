# StyleTag e2e test

* https://github.com/infection/infection/issues/2977

## Summary
When `PregMatchRemoveFlags` mutates a regex containing backslashes (e.g. `\|`, `\)`, `\w`),
the diff colorizer could produce a Symfony Console style tag (like `<diff-del-inline>`)
immediately preceded by a `\` from the PHP source. Symfony Console interprets `\<tag>` as an
escaped literal `<tag>`, skipping the opening tag but still processing the closing tag, which
causes "Incorrectly nested style tag found."

