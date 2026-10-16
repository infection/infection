# Source symbol selection

Verifies that the positional selector `Calculator::add` limits mutation generation to that method.
Both methods are covered: `add()` has one `Plus` mutation and `addThree()` has two.
Only the mutation in `add()` should be generated and killed.

The `Differ` cases exercise positional disambiguation with four covered classes:

- `SourceSymbolSelection\Differ\Differ` in `src/Differ/Differ.php`.
- `SourceSymbolSelection\Differ\Helper` in `src/Differ/Helper.php`.
- `SourceSymbolSelection\Other\Differ` in `src/Other/Namespaced.php`.
- Global `Differ` in `src/Other/Global.php`.

Each has one killed `Plus` mutation. Bare `Differ` generates only the mutation
in the matching class and path. `Differ/` selects both classes in that directory,
`Differ\` selects all three classes with that short name, and `\Differ` selects
only the global class. The readable directory `src/Differ` selects both files
without adding a symbol constraint.
