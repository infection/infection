<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use function array_keys;
use function array_slice;
use function count;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
final class UnwrapArrayUintersect extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an `array_uintersect` function call with each of its operands. For example:

```php
$x = array_uintersect(
    ['foo' => 'bar'],
    ['baz' => 'bar'],
    $value_compare_func
);
```

Will be mutated to:

```php
$x = ['foo' => 'bar'];
```

And into:

```php
$x = ['baz' => 'bar'];
```

TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = array_uintersect(
-     ['foo' => 'bar'],
-     ['baz' => 'bar'],
-     $value_compare_func
- );
# Mutation 1
+ $x = ['foo' => 'bar'];
# Mutation 2
+ $x = ['baz' => 'bar'];
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'array_uintersect';
    }
    /**
    @psalm-mutation-free
    */
    protected function getParameterIndexes(Node\Expr\FuncCall $node) : iterable
    {
        yield from array_slice(array_keys($node->args), 0, count($node->args) - 1);
    }
}
