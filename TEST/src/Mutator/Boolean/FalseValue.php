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
final class FalseValue implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition('Replaces a boolean literal (`false`) with its opposite value (`true`). ', MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $a = false;
+ $a = true;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Expr\ConstFetch(new Node\Name('true')));
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Expr\ConstFetch) {
            return \false;
        }
        return $node->name->toLowerString() === 'false';
    }
}
