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
final class ArrayItem implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a key-value pair (`[$key => $value]`) array declaration with a value array declaration
(`[$key > $value]`) where the key or the value are potentially impure (i.e. have a side-effect);
For example `[foo() => $b->bar]`.
TXT
, MutatorCategory::SEMANTIC_REDUCTION, <<<'TXT'
This mutation highlights the reliance of the side-effect(s) of the called key(s) and/or value(s)
- completely disregarding the actual values of the array. The array content should either be
checked or the impure calls should be made outside of the scope of the array.
TXT
, <<<'DIFF'
- $a = [$key => $value];
+ $a = [$key > $value]
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
        (yield new Node\Expr\BinaryOp\Greater($key, $value, $node->getAttributes()));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\ArrayItem && $node->key !== null && ($this->isNodeWithSideEffects($node->value) || $this->isNodeWithSideEffects($node->key));
    }
    private function isNodeWithSideEffects(Node $node) : bool
    {
        return $node instanceof Node\Expr\PropertyFetch || $node instanceof Node\Expr\MethodCall || $node instanceof Node\Expr\FuncCall;
    }
}
