<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use function array_keys;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
final class UnwrapArrayIntersect extends AbstractFunctionUnwrapMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an `array_intersect` function call with its operands. For example:

```php
$x = array_intersect($array1, $array2);
```

Will be mutated to:

```php
$x = $array1;
```

And:

```php
$x = $array2;
```

TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = array_intersect($array1, $array2);
# Mutation 1
+ $x = $array1;
# Mutation 2
+ $x = $array2;
DIFF
);
    }
    protected function getFunctionName() : string
    {
        return 'array_intersect';
    }
    /**
    @psalm-mutation-free
    */
    protected function getParameterIndexes(Node\Expr\FuncCall $node) : iterable
    {
        yield from array_keys($node->args);
    }
}
