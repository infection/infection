# Use `Memoized` over `Cached` for object-local result reuse

## Context

The codebase uses both `Memoized*` and `Cached*` names for implementations that
store a computed result on the object and reuse it for subsequent calls during
that object lifetime.

Examples include:

- `MemoizedCiDetector`
- `MemoizedComposerExecutableFinder`
- `MemoizedTestFileDataProvider`

These implementations perform memoization: the stored value is local to the
object lifetime, with no explicit invalidation, eviction, TTL, persistence or
shared cache backend.

For example:

- `MemoizedCiDetector` stores the detected CI server on the detector instance.
  There is no cache key and no way for callers to clear or warm the value.
- `MemoizedComposerExecutableFinder` wraps the finder result in a local deferred
  value. A second finder instance computes and stores its own result.
- `MemoizedTestFileDataProvider` stores results per test id in an object
  property. The stored data is not shared outside that provider instance.

`Cache` is a broader term. In this codebase it should describe a visible cache
lifecycle, such as storage backends, cache keys, invalidation, eviction, warmup,
cleanup, configuration, persistence across process runs or sharing across object
instances. Infection already uses cache terminology for these broader concerns:
PHPUnit result cache files, PHPStan cache, Rector cache and files under
`var/cache`, for example. In practice, for this codebase, exposing such a
lifecycle would mean using a [PSR-6] or [PSR-16] abstraction.

Using `Cached*` for object-local memoization makes these classes appear closer
to operational cache infrastructure than they are. `Memoized*` is more precise
and makes it clear that the behaviour is a local implementation detail, not a
cache lifecycle boundary.


## Decision

Use `Memoized*` rather than `Cached*` when naming classes and decorators that
hide a previous computation in their own object state and expose no cache
lifecycle to callers.

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

[PSR-6]: https://www.php-fig.org/psr/psr-6/
[PSR-16]: https://www.php-fig.org/psr/psr-16/
