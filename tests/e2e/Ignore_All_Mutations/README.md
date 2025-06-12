# Make sure Infection ignores all mutations given an annotation

https://github.com/infection/infection/issues/1231

Give this example Infection won't mutate or even look at anything inside this function:

```php
/** @infection-ignore-all */
public function doSomethingNastyButCostlyToRefactor() {

}
```

Likewise, given this annotation Infection won't consider anything in this loop:

```php
/** @infection-ignore-all */
foreach ($foo as $bar) {
    // 
}
```
