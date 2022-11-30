<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Cast;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
final class CastBool extends AbstractCastMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition('Removes an boolean cast operator (`(bool)`).', MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $a = (bool) $value;
+ $a = $value;
DIFF
);
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\Cast\Bool_;
    }
}
