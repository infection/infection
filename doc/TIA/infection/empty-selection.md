# Handling an empty explicit impact selection

A developer can combine an explicit impact query with a test filter. Their intersection
can legitimately contain no tests, as with a Calculator impact query and an UnrelatedTest
filter.

Infection currently treats PHPUnit's `No tests executed!` result as an initial-test
failure. The exit status, diagnostic and empty reports for this case remain undecided.
An empty result needs to be distinguished from missing or unusable impact data.

The [empty-intersection scenario][empty] fails and is tagged `@skip @decision_pending`.
Its expectation of a successful run with no tests or mutations is a proposal, not an
agreed outcome.

[empty]: ../../../tests/e2e/PHPUnit_TIA/features/05-configuration-and-boundaries.feature#L156
