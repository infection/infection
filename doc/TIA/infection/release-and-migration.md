# Replacing the experimental version gate and planning migration

The proof of concept pins PHPUnit PR #6919 at `e21b72d4`, reporting `13.5-dev`.
The current support gate matches only that version string, which does not identify the
pinned commit. A release needs a gate based on the TIA behaviour actually supported.

The intended way to select the recording strategy is through PHPUnit extra arguments.
Migration from `mapSourceClassToTestStrategy` remains unspecified.

There is no release or migration scenario. These are open integration tasks, not failures
reproduced by the current Behat suite.
