<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Mutator\FunctionSignature;

use Infection\Mutator\FunctionSignatureMutator;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;

class PublicVisibility extends FunctionSignatureMutator
{
    /**
     * Replaces "public function..." with "protected function ..."
     *
     * @param Node $node
     * @return ClassMethod
     */
    public function mutate(Node $node)
    {
        /** @var ClassMethod $node */
        return new ClassMethod(
            $node->name,
            [
                'flags' => Node\Stmt\Class_::MODIFIER_PROTECTED,
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

        if ($node->name === '__construct') {
            return false;
        }

        return $node->isPublic();
    }
}