<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
final class UnwrapArrayReduce extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an `array_reduce` function call with its first operand. For example:

```php
$x = array_reduce(
    ['foo', 'bar', 'baz'],
    static function ($carry, $item) {
       return $item;
    },
    ['oof']
);
```

Will be mutated to:

```php
$x = ['foo', 'bar', 'baz'];
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = array_reduce(
-     ['foo', 'bar', 'baz'],
-     static function ($carry, $item) {
-        return $item;
-     },
-     ['oof']
- );
+ $x = ['foo', 'bar', 'baz'];
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'array_reduce';
    }
    /**
    @psalm-mutation-free
    */
    protected function getParameterIndexes(Node\Expr\FuncCall $node) : iterable
    {
        (yield 2);
    }
}
