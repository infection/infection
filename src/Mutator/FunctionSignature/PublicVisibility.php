<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Mutator\FunctionSignature;

use Infection\Mutator\InterfaceParentTrait;
use Infection\Mutator\Mutator;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;

class PublicVisibility extends Mutator
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

    public function shouldMutate(Node $node): bool
    {
        if (!$node instanceof ClassMethod) {
            return false;
        }

        if ($this->isBlacklistedFunction($node->name)) {
            return false;
        }

        if ($this->isBelongsToInterface($node)) {
            return false;
        }

        return $node->isPublic();
    }

    private function isBlacklistedFunction(string $name): bool
    {
        return \in_array(
            $name,
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
            ],
            true
        );
    }
}
