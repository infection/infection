<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Removal;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class ConcatOperandRemoval implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Removes an operand from a string concatenation.

```php
$x = 'foo' . 'bar';
```

Will be mutated to:

```php
$x = 'foo';
```

And:

```php
$x = 'bar';

TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $x = 'foo' . 'bar';
# Mutation 1
+ $x = 'foo';
# Mutation 2
+ $x = 'bar';
DIFF
);
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\BinaryOp\Concat;
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        if ($node->left instanceof Node\Expr\BinaryOp\Concat) {
            (yield $node->left);
            return;
        }
        (yield $node->right);
        (yield $node->left);
    }
}
