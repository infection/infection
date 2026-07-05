# Use `Memoized` over `Cached` for object-local result reuse

## Context

The codebase uses both `Memoized*` and `Cached*` names for implementations that
store a computed result on the object and reuse it for subsequent calls during
that object's lifetime.

Examples include:

- `MemoizedCiDetector`
- `MemoizedComposerExecutableFinder`
- `MemoizedTestFileDataProvider`
- `CachedSourceCollector`

These implementations are memoization: the stored value is local to the object
lifetime, with no explicit invalidation, eviction, TTL, persistence or shared
cache backend.

`Cache` is a broader term and can imply concerns that do not exist in these
classes, such as storage backends, cache keys, invalidation, eviction, warmup,
cleanup, configuration, persistence across process runs or sharing across object
instances. Infection already uses cache terminology for these broader concerns:
PHPUnit result cache files, PHPStan cache, Rector cache and files under
`var/cache`, for example.

Using `Cached*` for object-local memoization makes these classes appear closer
to operational cache infrastructure than they are. `Memoized*` is more precise
and makes it clear that the behaviour is a local implementation detail, not a
cache lifecycle boundary.


## Decision

Use `Memoized*` rather than `Cached*` when naming classes and decorators whose
purpose is to reuse a previously computed value during the same object lifetime.

Reserve `Cached*` for implementations where the cache lifecycle is part of the
design: cache keys, invalidation, eviction, storage, warmup, cleanup,
persistence, sharing or user-visible cache configuration.

Properties and tests may use the name that best describes the local assertion or
data structure, but they should not introduce `Cached*` as the class-level
terminology for memoization decorators.

Existing `Cached*` names that represent object-local memoization should be
renamed to `Memoized*` when the affected code is next touched or when doing a
focused nomenclature cleanup.


## Status

Accepted.
