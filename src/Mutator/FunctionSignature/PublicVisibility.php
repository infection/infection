<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\FunctionSignature;

use Infection\Mutator\Util\InterfaceParentTrait;
use Infection\Mutator\Util\Mutator;
use Infection\Visitor\ReflectionVisitor;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;

final class PublicVisibility extends Mutator
{
    use InterfaceParentTrait;

    /**
     * Replaces "public function..." with "protected function ..."
     *
     * @param Node $node
     *
     * @return ClassMethod
     */
    public function mutate(Node $node)
    {
        /* @var ClassMethod $node */
        return new ClassMethod(
            $node->name,
            [
                'flags' => ($node->flags & ~Class_::MODIFIER_PUBLIC) | Class_::MODIFIER_PROTECTED,
                'byRef' => $node->returnsByRef(),
                'params' => $node->getParams(),
                'returnType' => $node->getReturnType(),
                'stmts' => $node->getStmts(),
            ],
            $node->getAttributes()
        );
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof ClassMethod) {
            return false;
        }

        if (!$node->isPublic()) {
            return false;
        }

        if ($node->isAbstract()) {
            return false;
        }

        if ($this->isBlacklistedFunction($node->name)) {
            return false;
        }

        if ($this->isBelongsToInterface($node)) {
            return false;
        }

        return !$this->hasSamePublicParentMethod($node);
    }

    private function isBlacklistedFunction(Node\Identifier $name): bool
    {
        return in_array(
            $name->name,
            [
                '__construct',
                '__invoke',
                '__call',
                '__callStatic',
                '__get',
                '__set',
                '__isset',
                '__unset',
                '__toString',
                '__debugInfo',
            ]
        );
    }

    private function hasSamePublicParentMethod(Node $node): bool
    {
        return $this->hasSamePublicMethodInInterface($node) || $this->hasSamePublicMethodInParentClass($node);
    }

    private function hasSamePublicMethodInInterface(Node $node): bool
    {
        /** @var \ReflectionClass $reflection */
        $reflection = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY);

        foreach ($reflection->getInterfaces() as $reflectionInterface) {
            try {
                $method = $reflectionInterface->getMethod($node->name->name);

                if ($method->isPublic()) {
                    // we can't mutate because interface requires the same public visibility
                    return true;
                }
            } catch (\ReflectionException $e) {
                continue;
            }
        }

        return false;
    }

    private function hasSamePublicMethodInParentClass(Node $node): bool
    {
        /** @var \ReflectionClass $reflection */
        $reflection = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY);

        $parent = $reflection->getParentClass();
        while ($parent) {
            try {
                $method = $parent->getMethod($node->name->name);

                if ($method->isPublic()) {
                    return true;
                }
            } catch (\ReflectionException $e) {
                continue;
            } finally {
                $parent = $parent->getParentClass();
            }
        }

        return false;
    }
}
