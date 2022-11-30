<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Util;

use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ReflectionVisitor;
use function is_string;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@template
@implements
*/
abstract class AbstractValueToNullReturnValue implements Mutator
{
    use GetMutatorName;
    protected function isNullReturnValueAllowed(Node $node) : bool
    {
        $functionScope = $node->getAttribute(ReflectionVisitor::FUNCTION_SCOPE_KEY, null);
        if ($functionScope === null) {
            return \true;
        }
        $returnType = $functionScope->getReturnType();
        if ($returnType instanceof Node\Identifier) {
            $returnType = $returnType->name;
        }
        if ($returnType === null) {
            return \true;
        }
        if (is_string($returnType)) {
            return \false;
        }
        if ($returnType instanceof Node\NullableType) {
            return \true;
        }
        return !$returnType instanceof Node\Name;
    }
}
