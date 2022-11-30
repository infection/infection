<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use function count;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use function range;
final class UnwrapArrayMap extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an `array_map` function call with its array operand. For example:

```php
$x = array_map($callback, ['foo', 'bar', 'baz']);
```

Will be mutated to:

```php
$x = ['foo', 'bar', 'baz'];
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = array_map($callback, ['foo', 'bar', 'baz']);
+ $x = ['foo', 'bar', 'baz'];
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'array_map';
    }
    /**
    @psalm-mutation-free
    */
    protected function getParameterIndexes(Node\Expr\FuncCall $node) : iterable
    {
        yield from range(1, count($node->args) - 1);
    }
}
