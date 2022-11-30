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
final class MethodCallRemoval implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition('Removes the method call.', MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $this->fooBar();
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Stmt\Nop());
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Stmt\Expression) {
            return \false;
        }
        return $node->expr instanceof Node\Expr\MethodCall || $node->expr instanceof Node\Expr\StaticCall;
    }
}
