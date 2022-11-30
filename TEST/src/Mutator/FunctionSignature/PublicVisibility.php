<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\FunctionSignature;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ReflectionVisitor;
use _HumbugBox9658796bb9f0\Infection\Reflection\ClassReflection;
use _HumbugBox9658796bb9f0\Infection\Reflection\Visibility;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class PublicVisibility implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition('Replaces the `public` method visibility keyword with `protected`.', MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- public function foo() {
+ protected function foo() {
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Stmt\ClassMethod($node->name, ['flags' => $node->flags & ~Node\Stmt\Class_::MODIFIER_PUBLIC | Node\Stmt\Class_::MODIFIER_PROTECTED, 'byRef' => $node->returnsByRef(), 'params' => $node->getParams(), 'returnType' => $node->getReturnType(), 'stmts' => $node->getStmts()], $node->getAttributes()));
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Stmt\ClassMethod) {
            return \false;
        }
        if (!$node->isPublic()) {
            return \false;
        }
        if ($node->isMagic()) {
            return \false;
        }
        return !$this->hasSamePublicParentMethod($node);
    }
    private function hasSamePublicParentMethod(Node\Stmt\ClassMethod $node) : bool
    {
        $reflection = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY);
        return $reflection->hasParentMethodWithVisibility($node->name->name, Visibility::asPublic());
    }
}
