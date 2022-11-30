<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Operator;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\PrettyPrinter\Standard;
/**
@implements
*/
final class Concat implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Flips the operands of the string concatenation operator `.`. For example:

```php
'foo' . 'bar';
```

Will be mutated to:

```php
'bar' . 'foo';
```
TXT
, MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- 'foo' . 'bar';
+ 'bar' . 'foo';
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        $printer = new Standard();
        if ($node->left instanceof Node\Expr\BinaryOp\Concat) {
            $left = new Node\Expr\BinaryOp\Concat($node->left->left, $node->right);
            $right = $node->left->right;
        } else {
            [$left, $right] = [$node->right, $node->left];
        }
        $newNode = new Node\Expr\BinaryOp\Concat($left, $right);
        if ($printer->prettyPrint([clone $node]) !== $printer->prettyPrint([$newNode])) {
            (yield $newNode);
        }
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\BinaryOp\Concat;
    }
}
