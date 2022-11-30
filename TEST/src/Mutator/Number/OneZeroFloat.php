<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Number;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@extends
*/
final class OneZeroFloat extends AbstractNumberMutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a zero float value (`0.0`) with a non-zero float value (`1.0`) and vice-versa.
TXT
, MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $a = 0.0;
+ $a = 1.0;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        if ($node->value === 0.0) {
            (yield new Node\Scalar\DNumber(1.0));
            return;
        }
        (yield new Node\Scalar\DNumber(0.0));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Scalar\DNumber && ($node->value === 0.0 || $node->value === 1.0) && !$this->isPartOfSizeComparison($node);
    }
}
