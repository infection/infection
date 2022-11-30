<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Operator;

use function count;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
/**
@implements
*/
final class SpreadAssignment implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Removes a spread operator in an array expression and turns it into an assignment. For example:

```php
$x = [...$collection];
```

Will be mutated to:

```php
$x = $collection;
```
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = [...$collection];
+ $x = $collection;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        Assert::allNotNull($node->items);
        (yield $node->items[0]->value);
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Expr\Array_) {
            return \false;
        }
        if (count($node->items) !== 1) {
            return \false;
        }
        Assert::allNotNull($node->items);
        return $node->items[0]->unpack;
    }
}
