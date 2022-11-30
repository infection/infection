<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
final class UnwrapArrayUdiffAssoc extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an `array_udiff_assoc` function call with its first operand. For example:

```php
$x = array_udiff_assoc(['foo' => 'bar'], ['baz' => 'bar'], $value_compare_func);
```

Will be mutated to:

```php
$x = ['foo => 'bar'];
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = array_udiff_assoc(['foo' => 'bar'], ['baz' => 'bar'], $value_compare_func);
+ $x = ['foo => 'bar'];
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'array_udiff_assoc';
    }
}
