<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Boolean;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class LogicalNot implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition('Removes a negation operator (`!`), e.g. transforms `!$foo` with `$foo`.', MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $a = !$b;
+ $a = $b;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield $node->expr);
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Expr\BooleanNot) {
            return \false;
        }
        $isDoubledLogicalNot = $node->expr instanceof Node\Expr\BooleanNot || $node->getAttribute('parent') instanceof Node\Expr\BooleanNot;
        return !$isDoubledLogicalNot;
    }
}
