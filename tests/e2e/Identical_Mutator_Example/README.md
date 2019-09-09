# Demo for IdenticalEqual/NotIdenticalNotEqual mutators

Typically one needs to use `===` and `!==` when there is a need to:

- Distinguish between `null`, `false`, `0`, and between `1` and `true`; `strpos` is a very well known companion of the identity operator.
- Where unwanted type juggling can be expected: string vs number comparison `1 == '1a'`, float vs. int, `'0.' == '0.0'`, and so on.

In many of these situations it easy to miss critical edge cases while writing tests.
These mutators let one see if their tests really exhaustive in regard of identical or not identical operators.
Otherwise put, if mutations from these mutators escape, tests are not good enough, which is also true for all other mutators.
