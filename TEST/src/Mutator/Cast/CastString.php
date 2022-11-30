<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Cast;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
final class CastString extends AbstractCastMutator
{
    public static function getDefinition() : ?Definition
    {
        return new Definition('Removes a string cast operator (`(string)`).', MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $a = (string) $value;
+ $a = $value;
DIFF
);
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\Cast\String_;
    }
}
