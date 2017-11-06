<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\FunctionSignature;

use Infection\Visitor\ReflectionVisitor;
use Infection\Mutator\Mutator;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;

class ProtectedVisibility extends Mutator
{
    /**
     * Replaces "protected function..." with "private function ..."
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
                'flags' => ($node->flags & ~Class_::MODIFIER_PROTECTED) | Class_::MODIFIER_PRIVATE,
                'byRef' => $node->returnsByRef(),
                'params' => $node->getParams(),
                'returnType' => $node->getReturnType(),
                'stmts' => $node->getStmts(),
            ],
            $node->getAttributes()
        );
    }

    public function shouldMutate(Node $node): bool
    {
        if (!$node instanceof ClassMethod) {
            return false;
        }

        if ($node->isAbstract()) {
            return false;
        }

        if ($this->hasSameProtectedParentMethod($node)) {
            return false;
        }

        return $node->isProtected();
    }

    private function hasSameProtectedParentMethod(Node $node): bool
    {
        /** @var \ReflectionClass $reflection */
        $reflection = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY);

        $parent = $reflection->getParentClass();
        while ($parent) {
            try {
                $method = $parent->getMethod($node->name);

                if ($method->isProtected()) {
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
