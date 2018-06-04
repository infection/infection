# Multiline statements

Relevant ticket: https://github.com/infection/infection/issues/366

## Discussion

When a calculation spans across multiple lines and infection does a mutation on it, it reports it as false positive, e.g. the original code:
```
$result = $value
    / 100
    * 100;

return $result;
```
and mutation:
```
$result = $value
    * 100
    * 100;

return $result;
```
This results in a false positive:
```
Not covered mutants:
====================

1) src/Calculator.php:9    [M] Division

--- Original
+++ New
@@ @@
 {
     public function calculateInMultipleLines(float $value) : float
     {
-        $result = $value / 100 * 100;
+        $result = $value * 100 * 100;
         return $result;
     }
     public function calculateInSingleLine(float $value) : float
```

## Code reuse rationale

[Code reused with permission from the author.](https://github.com/infection/infection/issues/366#issuecomment-394260435)

Original repository: https://github.com/trinet-at/infection-multiline
