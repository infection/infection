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
final class Yield_ implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a key-value pair (`yield $key => $value`) yielded value with the yielded value only
(without key) where the key or the value are potentially impure (i.e. have a side-effect); For
example `yield foo() => $b->bar;`.
TXT
, MutatorCategory::SEMANTIC_REDUCTION, <<<'TXT'
This mutation highlights the reliance of the side-effect(s) of the called key(s) and/or value(s)
- completely disregarding the actual yielded pair. The yielded content should either be checked or
the impure calls should be made outside of the scope of the yielded value.
TXT
, <<<'DIFF'
- yield $key => $value;
+ yield $key > $value;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        $key = $node->key;
        $value = $node->value;
        (yield new Node\Expr\Yield_(new Node\Expr\BinaryOp\Greater($key, $value, $node->getAttributes())));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\Yield_ && $node->key !== null;
    }
}
