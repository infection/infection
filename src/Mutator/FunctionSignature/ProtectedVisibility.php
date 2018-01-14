<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\FunctionSignature;

use Infection\Mutator\FunctionSignatureMutator;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Class_;

class ProtectedVisibility extends FunctionSignatureMutator
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

        return $node->isProtected();
    }
}
