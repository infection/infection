# Use `Memoized` over `Cached` for object-local result reuse

## Context

The codebase uses both `Memoized*` and `Cached*` names for implementations that
store a computed result on the object and reuse it for subsequent calls during
the object's lifetime.

`Memoized*` should describe object-local memoization. For example, a
`MemoizedProjectConfigurationLoader` may look like this:

```php
final class MemoizedProjectConfigurationLoader implements ProjectConfigurationLoader
{
    private ?ProjectConfiguration $projectConfiguration = null;

    public function __construct(
        private readonly ProjectConfigurationLoader $decoratedLoader,
    ) {
    }

    public function load(): ProjectConfiguration
    {
        return $this->projectConfiguration ??= $this->decoratedLoader->load();
    }
}
```

The decorated loader is called once per `MemoizedProjectConfigurationLoader`
instance, and subsequent calls made to that same instance return the stored
value. A second `MemoizedProjectConfigurationLoader` instance has its own stored
value. There is no cache key, invalidation, eviction, TTL, persistence, warmup,
cleanup or shared backend.

`Cached*` should describe a cache lifecycle that is part of the design. For
example, a `CachedProjectConfigurationLoader` may look like this:

```php
final class CachedProjectConfigurationLoader implements ProjectConfigurationLoader
{
    public function __construct(
        private readonly ProjectConfigurationLoader $decoratedLoader,
        private readonly CacheInterface $cache,
    ) {
    }

    public function load(): ProjectConfiguration
    {
        $cacheKey = 'project-configuration';
        $projectConfiguration = $this->cache->get($cacheKey);

        if ($projectConfiguration instanceof ProjectConfiguration) {
            return $projectConfiguration;
        }

        $projectConfiguration = $this->decoratedLoader->load();

        $this->cache->set($cacheKey, $projectConfiguration);

        return $projectConfiguration;
    }
}
```

The loader reads and writes values through a cache pool using explicit keys. The
stored values may be shared by multiple loader instances, persisted beyond one
object lifetime, configured, cleared, warmed or expired.

`Cache` is a broader term. In this codebase it should describe a visible cache
lifecycle, such as storage backends, cache keys, invalidation, eviction, warmup,
cleanup, configuration, persistence across process runs or sharing across object
instances. Infection already uses cache terminology for these broader concerns:
PHPUnit result cache files, PHPStan cache, Rector cache and files under
`var/cache`, for example. In practice, for this codebase, exposing such a
lifecycle would mean using a [PSR-6] or [PSR-16] abstraction.

Using `Cached*` for object-local memoization makes an implementation appear
closer to operational cache infrastructure than it is. `Memoized*` is more
precise: it communicates that the reuse is a local implementation detail, not a
cache lifecycle boundary that callers can observe or manage.


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
