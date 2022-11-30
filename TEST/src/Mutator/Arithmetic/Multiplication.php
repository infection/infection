<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Arithmetic;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ParentConnector;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ReflectionVisitor;
use function is_string;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class Multiplication implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a multiplication operator (`*`) with a division assignment operator (`/`).
TXT
, MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $a = $b * $c;
+ $a = $b / $c;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Expr\BinaryOp\Div($node->left, $node->right, $node->getAttributes()));
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Expr\BinaryOp\Mul) {
            return \false;
        }
        if ($this->isNumericOne($node->left) || $this->isNumericOne($node->right)) {
            return \false;
        }
        if ($node->left instanceof Node\Expr\UnaryMinus && $this->isNumericOne($node->left->expr)) {
            return \false;
        }
        if ($node->right instanceof Node\Expr\UnaryMinus && $this->isNumericOne($node->right->expr)) {
            return \false;
        }
        $functionScope = $node->getAttribute(ReflectionVisitor::FUNCTION_SCOPE_KEY);
        if (!$functionScope instanceof Node\Stmt\ClassMethod) {
            return \true;
        }
        $parentNode = ParentConnector::getParent($node);
        if (!$parentNode instanceof Node\Stmt\Return_) {
            return \true;
        }
        $returnType = $functionScope->getReturnType();
        if ($returnType instanceof Node\Identifier) {
            $returnType = $returnType->name;
        }
        return !(is_string($returnType) && $returnType === 'int');
    }
    private function isNumericOne(Node $node) : bool
    {
        if ($node instanceof Node\Scalar\LNumber && $node->value === 1) {
            return \true;
        }
        return $node instanceof Node\Scalar\DNumber && $node->value === 1.0;
    }
}
