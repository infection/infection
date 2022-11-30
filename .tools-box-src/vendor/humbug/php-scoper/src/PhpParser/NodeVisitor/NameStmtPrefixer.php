<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\Node\FullyQualifiedFactory;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\AttributeAppender\ParentNodeAppender;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceStmtCollection;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\OriginalNameResolver;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt\UseStmtCollection;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\UseStmtName;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\EnrichedReflector;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Attribute;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\ArrowFunction;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\ClassConstFetch;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\Closure;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\ConstFetch;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\FuncCall;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\Instanceof_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\New_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\StaticCall;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\StaticPropertyFetch;
use _HumbugBoxb47773b41c19\PhpParser\Node\IntersectionType;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name\FullyQualified;
use _HumbugBoxb47773b41c19\PhpParser\Node\NullableType;
use _HumbugBoxb47773b41c19\PhpParser\Node\Param;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Catch_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Class_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\ClassMethod;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Function_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Interface_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Property;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\TraitUse;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\TraitUseAdaptation\Alias;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\TraitUseAdaptation\Precedence;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Use_;
use _HumbugBoxb47773b41c19\PhpParser\Node\UnionType;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
use function in_array;
use function strtolower;
final class NameStmtPrefixer extends NodeVisitorAbstract
{
    private const SUPPORTED_PARENT_NODE_CLASS_NAMES = [Alias::class, Attribute::class, ArrowFunction::class, Catch_::class, ConstFetch::class, Class_::class, ClassConstFetch::class, ClassMethod::class, Closure::class, FuncCall::class, Function_::class, Instanceof_::class, Interface_::class, New_::class, Param::class, Precedence::class, Property::class, StaticCall::class, StaticPropertyFetch::class, TraitUse::class, UnionType::class, IntersectionType::class];
    public function __construct(private readonly string $prefix, private readonly NamespaceStmtCollection $namespaceStatements, private readonly UseStmtCollection $useStatements, private readonly EnrichedReflector $enrichedReflector)
    {
    }
    public function enterNode(Node $node) : Node
    {
        if (!$node instanceof Name) {
            return $node;
        }
        return $this->prefixName($node, self::getParent($node));
    }
    private static function getParent(Node $name) : Node
    {
        $parent = ParentNodeAppender::getParent($name);
        if (!$parent instanceof NullableType) {
            return $parent;
        }
        return self::getParent($parent);
    }
    private function prefixName(Name $resolvedName, Node $parentNode) : Node
    {
        if ($resolvedName->isSpecialClassName() || !self::isParentNodeSupported($parentNode)) {
            return $resolvedName;
        }
        $originalName = OriginalNameResolver::getOriginalName($resolvedName);
        if ($parentNode instanceof ConstFetch && 'null' === $originalName->toLowerString()) {
            return $originalName;
        }
        $useStatement = $this->useStatements->findStatementForNode($this->namespaceStatements->findNamespaceForNode($resolvedName), $resolvedName);
        if ($this->doesNameHasUseStatement($originalName, $resolvedName, $parentNode, $useStatement)) {
            return $originalName;
        }
        if ($this->isNamePrefixable($resolvedName)) {
            return $resolvedName;
        }
        $currentNamespace = $this->namespaceStatements->getCurrentNamespaceName();
        if (self::doesNameBelongToNamespace($originalName, $resolvedName, $currentNamespace) || $this->doesNameBelongToGlobalNamespace($originalName, $resolvedName->toString(), $parentNode, $currentNamespace)) {
            return $originalName;
        }
        if (!$this->isPrefixableClassName($resolvedName, $parentNode)) {
            return $resolvedName;
        }
        if ($parentNode instanceof ConstFetch) {
            $prefixedName = $this->prefixConstFetchNode($resolvedName);
            if (null !== $prefixedName) {
                return $prefixedName;
            }
        }
        if ($parentNode instanceof FuncCall) {
            $prefixedName = $this->prefixFuncCallNode($originalName, $resolvedName);
            if (null !== $prefixedName) {
                return $prefixedName;
            }
        }
        return FullyQualifiedFactory::concat($this->prefix, $resolvedName->toString(), $resolvedName->getAttributes());
    }
    private static function isParentNodeSupported(Node $parentNode) : bool
    {
        foreach (self::SUPPORTED_PARENT_NODE_CLASS_NAMES as $supportedClassName) {
            if ($parentNode instanceof $supportedClassName) {
                return \true;
            }
        }
        return \false;
    }
    private function isNamePrefixable(Name $resolvedName) : bool
    {
        if (!$resolvedName->isFullyQualified()) {
            return \false;
        }
        $isAlreadyPrefixed = $this->prefix === $resolvedName->getFirst();
        return $isAlreadyPrefixed || $this->enrichedReflector->belongsToExcludedNamespace((string) $resolvedName);
    }
    private static function doesNameBelongToNamespace(Name $originalName, Name $resolvedName, ?Name $namespaceName) : bool
    {
        if ($namespaceName === null || !$resolvedName->isFullyQualified() || $originalName->isFullyQualified()) {
            return \false;
        }
        $originalNameFQParts = [...$namespaceName->parts, ...$originalName->parts];
        return $originalNameFQParts === $resolvedName->parts;
    }
    private function doesNameBelongToGlobalNamespace(Name $originalName, string $resolvedName, Node $parentNode, ?Name $namespaceName) : bool
    {
        return null === $namespaceName && !$originalName->isFullyQualified() && !$parentNode instanceof ConstFetch && (!$this->enrichedReflector->isExposedClass($resolvedName) || $this->enrichedReflector->isExposedClassFromGlobalNamespace($resolvedName)) && !$this->enrichedReflector->isClassExcluded($resolvedName) && (!$this->enrichedReflector->isExposedFunction($resolvedName) || $this->enrichedReflector->isExposedFunctionFromGlobalNamespace($resolvedName)) && !$this->enrichedReflector->isFunctionExcluded($resolvedName);
    }
    private function doesNameHasUseStatement(Name $originalName, Name $resolvedName, Node $parentNode, ?Name $useStatementName) : bool
    {
        if (null === $useStatementName || !$resolvedName->isFullyQualified() || $originalName->isFullyQualified()) {
            return \false;
        }
        $useStmt = new UseStmtName($useStatementName);
        if (!$useStmt->contains($resolvedName)) {
            return \false;
        }
        [$useStmtAlias, $useStmtType] = $useStmt->getUseStmtAliasAndType();
        if ($parentNode instanceof ConstFetch) {
            $isExposedConstant = $this->enrichedReflector->isExposedConstant($resolvedName->toString());
            return $isExposedConstant && Use_::TYPE_CONSTANT === $useStmtType || !$isExposedConstant;
        }
        if (null === $useStmtAlias) {
            return \true;
        }
        $caseSensitiveUseStmt = !in_array($useStmtType, [Use_::TYPE_UNKNOWN, Use_::TYPE_NORMAL], \true);
        return $caseSensitiveUseStmt ? $originalName->getFirst() === $useStmtAlias : strtolower($originalName->getFirst()) === strtolower($useStmtAlias);
    }
    private function isPrefixableClassName(Name $resolvedName, Node $parentNode) : bool
    {
        $isClassNode = $parentNode instanceof ConstFetch || $parentNode instanceof FuncCall;
        return $isClassNode || !$resolvedName->isFullyQualified() || !$this->enrichedReflector->isClassExcluded($resolvedName->toString());
    }
    private function prefixConstFetchNode(Name $resolvedName) : ?Name
    {
        $resolvedNameString = $resolvedName->toString();
        if ($resolvedName->isFullyQualified()) {
            return $this->enrichedReflector->isExposedConstant($resolvedNameString) ? $resolvedName : null;
        }
        if ($this->enrichedReflector->isConstantInternal($resolvedNameString)) {
            return new FullyQualified($resolvedNameString, $resolvedName->getAttributes());
        }
        if ($this->enrichedReflector->isExposedConstant($resolvedNameString)) {
            return $this->enrichedReflector->isExposedConstantFromGlobalNamespace($resolvedNameString) ? $resolvedName : new FullyQualified($resolvedNameString, $resolvedName->getAttributes());
        }
        return $resolvedName;
    }
    private function prefixFuncCallNode(Name $originalName, Name $resolvedName) : ?Name
    {
        $resolvedNameString = $resolvedName->toString();
        if ($resolvedName->isFullyQualified()) {
            return $this->enrichedReflector->isFunctionExcluded($resolvedNameString) ? $resolvedName : null;
        }
        if ($this->enrichedReflector->isFunctionInternal($resolvedNameString)) {
            return new FullyQualified($originalName->toString(), $originalName->getAttributes());
        }
        if ($this->enrichedReflector->isExposedFunction($resolvedNameString)) {
            return $this->enrichedReflector->isExposedFunctionFromGlobalNamespace($resolvedNameString) ? $resolvedName : null;
        }
        return $resolvedName;
    }
}
