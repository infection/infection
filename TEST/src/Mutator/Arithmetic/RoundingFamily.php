<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Arithmetic;

use function array_diff;
use function in_array;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class RoundingFamily implements Mutator
{
    use GetMutatorName;
    private const MUTATORS_MAP = ['floor', 'ceil', 'round'];
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces rounding operations. For example `floor()` will be replaced with `ceil()` and `round()`.
TXT
, MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $a = floor($b);
# Mutation 1
+ $a = ceil($b);
# Mutation 2
+ $a = round($b);
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        $name = $node->name;
        /**
        @psalm-suppress */
        $currentFunctionName = $name->toLowerString();
        $mutateToFunctions = array_diff(self::MUTATORS_MAP, [$currentFunctionName]);
        foreach ($mutateToFunctions as $functionName) {
            (yield new Node\Expr\FuncCall(new Node\Name($functionName), [$node->args[0]], $node->getAttributes()));
        }
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Expr\FuncCall) {
            return \false;
        }
        if (!$node->name instanceof Node\Name || !in_array($node->name->toLowerString(), self::MUTATORS_MAP, \true)) {
            return \false;
        }
        return \true;
    }
}
