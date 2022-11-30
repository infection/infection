<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

use DomainException;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
/**
@template
@implements
*/
final class NoopMutator implements Mutator
{
    public function __construct(private Mutator $mutator)
    {
    }
    public static function getDefinition() : ?Definition
    {
        throw new DomainException(sprintf('The class "%s" does not have a definition', self::class));
    }
    public function canMutate(Node $node) : bool
    {
        return $this->mutator->canMutate($node);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield $node);
    }
    public function getName() : string
    {
        return $this->mutator->getName();
    }
}
