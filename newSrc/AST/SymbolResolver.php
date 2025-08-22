<?php

declare(strict_types=1);

namespace newSrc\AST;

use Infection\PhpParser\Visitor\ParentConnector;
use newSrc\TestFramework\Trace\Symbol\ClassReference;
use newSrc\TestFramework\Trace\Symbol\FunctionReference;
use newSrc\TestFramework\Trace\Symbol\MethodReference;
use newSrc\TestFramework\Trace\Symbol\NamespaceReference;
use newSrc\TestFramework\Trace\Symbol\Symbol;
use PhpParser\Node;
use Webmozart\Assert\Assert;
use function sprintf;

final class SymbolResolver
{
    public function tryToResolve(Node $node): ?Symbol
    {
        return match(true) {
            $node instanceof Node\Stmt\Namespace_ && null !== $node->name => new NamespaceReference(
                $node->name->toString(),
            ),
            $node instanceof Node\Stmt\Function_ => new FunctionReference(
                $node->name->toString(),
            ),
            $node instanceof Node\Stmt\Class_ => new ClassReference(
                $node->namespacedName->toString(),
            ),
            $node instanceof Node\Stmt\ClassMethod => new MethodReference(
                sprintf(
                    '%s::%s()',
                    self::getClassName($node)->toString(),
                    $node->name->toString(),
                ),
            ),
            default => null,
        };
    }

    private static function getClassName(Node\Stmt\ClassMethod $classMethod): Node\Name
    {
        /** @var Node\Stmt\Class_ $parent */
        $parent = ParentConnector::getParent($classMethod);
        Assert::isInstanceOf($parent, Node\Stmt\Class_::class);

        return $parent->namespacedName;
    }
}
