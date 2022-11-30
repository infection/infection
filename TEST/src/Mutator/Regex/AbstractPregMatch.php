<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Regex;

use Generator;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\Node\Expr\FuncCall;
use function strtolower;
/**
@implements
*/
abstract class AbstractPregMatch implements Mutator
{
    use GetMutatorName;
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        if ($node->args[0] instanceof Node\VariadicPlaceholder) {
            return [];
        }
        $originalRegex = $this->pullOutRegex($node->args[0]);
        foreach ($this->mutateRegex($originalRegex) as $mutatedRegex) {
            $newArgument = $this->getNewRegexArgument($mutatedRegex, $node->args[0]);
            (yield new FuncCall($node->name, [$newArgument] + $node->args, $node->getAttributes()));
        }
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof FuncCall && $node->name instanceof Node\Name && strtolower((string) $node->name) === 'preg_match' && $node->args[0] instanceof Node\Arg && $node->args[0]->value instanceof Node\Scalar\String_ && $this->isProperRegexToMutate($this->pullOutRegex($node->args[0]));
    }
    protected abstract function isProperRegexToMutate(string $regex) : bool;
    /**
    @psalm-mutation-free
    */
    protected abstract function mutateRegex(string $regex) : Generator;
    /**
    @psalm-mutation-free
    */
    private function pullOutRegex(Node\Arg $argument) : string
    {
        $stringNode = $argument->value;
        return $stringNode->value;
    }
    /**
    @psalm-mutation-free
    */
    private function getNewRegexArgument(string $regex, Node\Arg $argument) : Node\Arg
    {
        return new Node\Arg(new Node\Scalar\String_($regex, $argument->value->getAttributes()), $argument->byRef, $argument->unpack, $argument->getAttributes());
    }
}
