# Debug events reporter

Runs a real PHPUnit suite with one killed mutant and `--log-verbosity=none`.
Checks that `logs.debugEvents` replaces a previous trace, writes valid JSONL records
with consecutive sequence numbers, and captures events in dispatch order through
`MutationTestingWasFinished`, including the mutant's detection status.

Progress events depend on how subprocess output is buffered, so their count is not fixed.
