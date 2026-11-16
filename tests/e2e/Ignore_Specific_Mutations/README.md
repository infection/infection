# Ignore specific mutations

`@infection-ignore ArrayItemRemoval, FalseValue` excludes the listed mutators
on the annotated node and its descendants. Other mutators remain enabled.

Spaces around commas are optional: `@infection-ignore ArrayItemRemoval,FalseValue`
and `@infection-ignore   ArrayItemRemoval   ,   FalseValue` are also accepted.

The fixture expects six killed mutants: `TrueValue` and `PublicVisibility` on
`selectivelyIgnored()`, and all four configured mutators on the following
unannotated `notIgnored()` method. This checks that exclusions stop when leaving
the annotated subtree. Excluded mutations are never generated, so the `Ignored`
result count remains zero.

`FalseValue` has a configuration-based ignore rule for an unrelated class,
checking that annotations also work with the configured mutator wrapper.
The annotation includes `UnknownMutator`, which produces a warning while the
run continues.

Names are exact and case-sensitive. Built-in names are recognized even when
disabled, along with configured custom names. Profiles are not expanded.
Each annotation's comma-separated list occupies one comment line; both docblocks
and ordinary comments are supported. Unknown names and annotations without any
mutator names produce warnings with the source file and annotated node's line.

Nested annotations add exclusions to those inherited from the enclosing node.
An annotation on an array item does not exclude mutations targeting its parent
array; annotate the array or an enclosing statement to exclude `ArrayItemRemoval`.
