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
final class InstanceOf_ implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition('Replaces an instanceof comparison with `true` and `false`.', MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $a = $b instanceof User;
# Mutation 1
+ $a = true;
# Mutation 2
+ $a = false;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Expr\ConstFetch(new Node\Name('true')));
        (yield new Node\Expr\ConstFetch(new Node\Name('false')));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\Instanceof_;
    }
}
