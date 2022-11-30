<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\Node\FullyQualifiedFactory;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\AttributeAppender\ParentNodeAppender;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\IdentifierResolver;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\UnexpectedParsingScenario;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\EnrichedReflector;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\SymbolsRegistry;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Arg;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\FuncCall;
use _HumbugBoxb47773b41c19\PhpParser\Node\Identifier;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name\FullyQualified;
use _HumbugBoxb47773b41c19\PhpParser\Node\Scalar\String_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Function_;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
final class FunctionIdentifierRecorder extends NodeVisitorAbstract
{
    public function __construct(private readonly string $prefix, private readonly IdentifierResolver $identifierResolver, private readonly SymbolsRegistry $symbolsRegistry, private readonly EnrichedReflector $enrichedReflector)
    {
    }
    public function enterNode(Node $node) : Node
    {
        if (!($node instanceof Identifier || $node instanceof Name || $node instanceof String_) || !ParentNodeAppender::hasParent($node)) {
            return $node;
        }
        $resolvedName = $this->retrieveResolvedName($node);
        if (null !== $resolvedName && $this->shouldBeAliased($node, $resolvedName)) {
            $this->symbolsRegistry->recordFunction($resolvedName, FullyQualifiedFactory::concat($this->prefix, $resolvedName));
        }
        return $node;
    }
    private function shouldBeAliased(Node $node, FullyQualified $resolvedName) : bool
    {
        if ($this->enrichedReflector->isExposedFunction($resolvedName->toString())) {
            return \true;
        }
        return self::isFunctionDeclaration($node) && $this->enrichedReflector->belongsToGlobalNamespace($resolvedName->toString()) && $this->enrichedReflector->isFunctionExcluded($resolvedName->toString());
    }
    private function retrieveResolvedName(Node $node) : ?FullyQualified
    {
        if ($node instanceof Identifier) {
            return $this->retrieveResolvedNameForIdentifier($node);
        }
        if ($node instanceof Name) {
            return $this->retrieveResolvedNameForFuncCall($node);
        }
        if ($node instanceof String_) {
            return $this->retrieveResolvedNameForString($node);
        }
        throw UnexpectedParsingScenario::create();
    }
    private function retrieveResolvedNameForIdentifier(Identifier $identifier) : ?FullyQualified
    {
        $parent = ParentNodeAppender::getParent($identifier);
        if (!$parent instanceof Function_ || $identifier === $parent->returnType) {
            return null;
        }
        $resolvedName = $this->identifierResolver->resolveIdentifier($identifier);
        return $resolvedName instanceof FullyQualified ? $resolvedName : null;
    }
    private function retrieveResolvedNameForFuncCall(Name $name) : ?FullyQualified
    {
        $parent = ParentNodeAppender::getParent($name);
        if (!$parent instanceof FuncCall) {
            return null;
        }
        return $name instanceof FullyQualified ? $name : null;
    }
    private function retrieveResolvedNameForString(String_ $string) : ?FullyQualified
    {
        $stringParent = ParentNodeAppender::getParent($string);
        if (!$stringParent instanceof Arg) {
            return null;
        }
        $argParent = ParentNodeAppender::getParent($stringParent);
        if (!$argParent instanceof FuncCall) {
            return null;
        }
        if (!self::isFunctionExistsCall($argParent)) {
            return null;
        }
        $resolvedName = $this->identifierResolver->resolveString($string);
        return $resolvedName instanceof FullyQualified ? $resolvedName : null;
    }
    private static function isFunctionExistsCall(FuncCall $node) : bool
    {
        $name = $node->name;
        return $name instanceof Name && $name->isFullyQualified() && $name->toString() === 'function_exists';
    }
    private static function isFunctionDeclaration(Node $node) : bool
    {
        if (!$node instanceof Identifier) {
            return \false;
        }
        $parentNode = ParentNodeAppender::getParent($node);
        return $parentNode instanceof Function_;
    }
}
