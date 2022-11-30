<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Unwrap;

use function array_key_exists;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use function strtolower;
/**
@implements
*/
abstract class AbstractFunctionUnwrapMutator implements Mutator
{
    use GetMutatorName;
    /**
    @psalm-mutation-free
    */
    public final function mutate(Node $node) : iterable
    {
        foreach ($this->getParameterIndexes($node) as $index) {
            if ($node->args[$index] instanceof Node\VariadicPlaceholder) {
                continue;
            }
            if ($node->args[$index]->unpack) {
                continue;
            }
            (yield $node->args[$index]);
        }
    }
    public final function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Expr\FuncCall || !$node->name instanceof Node\Name) {
            return \false;
        }
        foreach ($this->getParameterIndexes($node) as $index) {
            if (!array_key_exists($index, $node->args)) {
                return \false;
            }
        }
        return $node->name->toLowerString() === strtolower($this->getFunctionName());
    }
    protected abstract function getFunctionName() : string;
    /**
    @psalm-mutation-free
    */
    protected function getParameterIndexes(Node\Expr\FuncCall $node) : iterable
    {
        (yield 0);
    }
}
