# Title

* Issue [#459](https://github.com/infection/infection/issues/459)
* Relates To PR [#457](https://github.com/infection/infection/pull/457)

## Summary
Testing To Ensure Errors Have Time To Output Fully

## Full Ticket
When the testing runs, it previously killed the initial tests as soon as any error output was detected.
This created issues in CI environments where the full error could not be retried and instead got an error code 143.
