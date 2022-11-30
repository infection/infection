<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor;

use function array_pop;
use function count;
use _HumbugBox9658796bb9f0\Infection\Reflection\AnonymousClassReflection;
use _HumbugBox9658796bb9f0\Infection\Reflection\ClassReflection;
use _HumbugBox9658796bb9f0\Infection\Reflection\CoreClassReflection;
use _HumbugBox9658796bb9f0\Infection\Reflection\NullReflection;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\NodeTraverser;
use _HumbugBox9658796bb9f0\PhpParser\NodeVisitorAbstract;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class ReflectionVisitor extends NodeVisitorAbstract
{
    public const REFLECTION_CLASS_KEY = 'reflectionClass';
    public const IS_INSIDE_FUNCTION_KEY = 'isInsideFunction';
    public const IS_ON_FUNCTION_SIGNATURE = 'isOnFunctionSignature';
    public const FUNCTION_SCOPE_KEY = 'functionScope';
    public const FUNCTION_NAME = 'functionName';
    private array $functionScopeStack = [];
    private array $classScopeStack = [];
    private ?string $methodName = null;
    public function beforeTraverse(array $nodes) : ?array
    {
        $this->functionScopeStack = [];
        $this->classScopeStack = [];
        $this->methodName = null;
        return null;
    }
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassLike) {
            $this->classScopeStack[] = $this->getClassReflectionForNode($node);
        }
        if (count($this->classScopeStack) === 0) {
            return null;
        }
        if ($node instanceof Node\Stmt\ClassMethod) {
            $this->methodName = $node->name->name;
        }
        $isInsideFunction = $this->isInsideFunction($node);
        if ($isInsideFunction) {
            $node->setAttribute(self::IS_INSIDE_FUNCTION_KEY, \true);
        } elseif ($node instanceof Node\Stmt\Function_) {
            return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        }
        if ($this->isPartOfFunctionSignature($node)) {
            $node->setAttribute(self::IS_ON_FUNCTION_SIGNATURE, \true);
        }
        if ($this->isFunctionLikeNode($node)) {
            $this->functionScopeStack[] = $node;
            $node->setAttribute(self::REFLECTION_CLASS_KEY, $this->classScopeStack[count($this->classScopeStack) - 1]);
            $node->setAttribute(self::FUNCTION_NAME, $this->methodName);
        } elseif ($isInsideFunction) {
            $node->setAttribute(self::FUNCTION_SCOPE_KEY, $this->functionScopeStack[count($this->functionScopeStack) - 1]);
            $node->setAttribute(self::REFLECTION_CLASS_KEY, $this->classScopeStack[count($this->classScopeStack) - 1]);
            $node->setAttribute(self::FUNCTION_NAME, $this->methodName);
        }
        return null;
    }
    public function leaveNode(Node $node) : ?Node
    {
        if ($this->isFunctionLikeNode($node)) {
            array_pop($this->functionScopeStack);
        }
        if ($node instanceof Node\Stmt\ClassLike) {
            array_pop($this->classScopeStack);
        }
        return null;
    }
    private function isPartOfFunctionSignature(Node $node) : bool
    {
        if ($this->isFunctionLikeNode($node)) {
            return \true;
        }
        $parent = ParentConnector::findParent($node);
        if ($parent === null) {
            return \false;
        }
        return $parent instanceof Node\Param || $node instanceof Node\Param;
    }
    private function isInsideFunction(Node $node) : bool
    {
        $parent = ParentConnector::findParent($node);
        if ($parent === null) {
            return \false;
        }
        if ($parent->getAttribute(self::IS_INSIDE_FUNCTION_KEY) !== null) {
            return \true;
        }
        if ($this->isFunctionLikeNode($parent)) {
            return \true;
        }
        return $this->isInsideFunction($parent);
    }
    private function isFunctionLikeNode(Node $node) : bool
    {
        if ($node instanceof Node\Stmt\ClassMethod) {
            return \true;
        }
        if ($node instanceof Node\Expr\Closure) {
            return \true;
        }
        return \false;
    }
    private function getClassReflectionForNode(Node\Stmt\ClassLike $node) : ClassReflection
    {
        $fqn = FullyQualifiedClassNameManipulator::getFqcn($node);
        if ($fqn !== null) {
            return CoreClassReflection::fromClassName($fqn->toString());
        }
        Assert::isInstanceOf($node, Node\Stmt\Class_::class);
        $extends = $node->extends;
        if ($extends !== null) {
            $name = $extends->getAttribute('resolvedName');
            Assert::isInstanceOf($name, Node\Name::class);
            return AnonymousClassReflection::fromClassName($name->toString());
        }
        return new NullReflection();
    }
}
