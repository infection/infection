<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

use DomainException;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ReflectionVisitor;
use _HumbugBox9658796bb9f0\Infection\Reflection\ClassReflection;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
/**
@template
@implements
*/
final class IgnoreMutator implements Mutator
{
    public function __construct(private IgnoreConfig $config, private Mutator $mutator)
    {
    }
    public static function getDefinition() : ?Definition
    {
        throw new DomainException(sprintf('The class "%s" does not have a definition', self::class));
    }
    public function canMutate(Node $node) : bool
    {
        if (!$this->mutator->canMutate($node)) {
            return \false;
        }
        $reflectionClass = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY);
        if (!$reflectionClass instanceof ClassReflection) {
            return \true;
        }
        return !$this->config->isIgnored($reflectionClass->getName(), $node->getAttribute(ReflectionVisitor::FUNCTION_NAME, ''), $node->getLine());
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        return $this->mutator->mutate($node);
    }
    public function getName() : string
    {
        return $this->mutator->getName();
    }
}
