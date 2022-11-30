<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\ReturnValue;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\Infection\Mutator\Util\AbstractValueToNullReturnValue;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@extends
*/
final class This extends AbstractValueToNullReturnValue
{
    public static function getDefinition() : ?Definition
    {
        return new Definition('Replaces a `return $this` statement with `return null` instead.', MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
class X {
    function foo()
    {
-        return $this;
+        return null;
    }
}
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Stmt\Return_(new Node\Expr\ConstFetch(new Node\Name('null'))));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Stmt\Return_ && $node->expr instanceof Node\Expr\Variable && $node->expr->name === 'this' && $this->isNullReturnValueAllowed($node);
    }
}
