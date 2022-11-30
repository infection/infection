<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Operator;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class SpreadRemoval implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Removes a spread operator in an array expression. For example:

```php
$x = [...$collection, 4, 5];
```

Will be mutated to:

```php
$x = [$collection, 4, 5];
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = [...$collection, 4, 5];
+ $x = [$collection, 4, 5];
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Expr\ArrayItem($node->value, null, \false, $node->getAttributes(), \false));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\ArrayItem && $node->unpack;
    }
}
