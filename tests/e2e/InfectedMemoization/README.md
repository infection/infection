# Demo to show "arid code" aspect of memoization

* https://github.com/infection/infection/issues/2268

## Is your feature request related to a problem?

I'm looking at many instances of this memoization pattern:

```php
private ?object $config = null;

private function getConfig(): object
{
    if (null !== $this->config) {
        return $this->config;
    }
    // ... load and cache
    $this->config = $config;
    return $this->config;
}
```
And there's no way to see this memoization is not tested by looking at mutation testing results only.

## Describe the solution you'd like.

I'd love to see a mutation that would remove one of the return statements if there are several.

```diff
    if (null !== $this->config) {
-        return $this->config;
    }
    // ...
    return $this->config;
```

The missing test should install a spy on `loadConfig()` to confirm it only called once.

Alternatively, we can remove a whole if-return branch:

```diff
-   if (null !== $this->config) {
-        return $this->config;
-   }
    // ...
    return $this->config;
```

