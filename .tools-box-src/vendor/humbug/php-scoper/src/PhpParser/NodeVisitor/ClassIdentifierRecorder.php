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
use _HumbugBoxb47773b41c19\PhpParser\Node\Identifier;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name\FullyQualified;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Class_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Interface_;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
final class ClassIdentifierRecorder extends NodeVisitorAbstract
{
    public function __construct(private readonly string $prefix, private readonly IdentifierResolver $identifierResolver, private readonly SymbolsRegistry $symbolsRegistry, private readonly EnrichedReflector $enrichedReflector)
    {
    }
    public function enterNode(Node $node) : Node
    {
        if (!$node instanceof Identifier || !ParentNodeAppender::hasParent($node)) {
            return $node;
        }
        $parent = ParentNodeAppender::getParent($node);
        $isClassOrInterface = $parent instanceof Class_ || $parent instanceof Interface_;
        if (!$isClassOrInterface) {
            return $node;
        }
        if (null === $parent->name) {
            throw UnexpectedParsingScenario::create();
        }
        $resolvedName = $this->identifierResolver->resolveIdentifier($node);
        if (!$resolvedName instanceof FullyQualified) {
            throw UnexpectedParsingScenario::create();
        }
        if ($this->shouldBeAliased($resolvedName->toString())) {
            $this->symbolsRegistry->recordClass($resolvedName, FullyQualifiedFactory::concat($this->prefix, $resolvedName));
        }
        return $node;
    }
    private function shouldBeAliased(string $resolvedName) : bool
    {
        if ($this->enrichedReflector->isExposedClass($resolvedName)) {
            return \true;
        }
        return $this->enrichedReflector->belongsToGlobalNamespace($resolvedName) && $this->enrichedReflector->isClassExcluded($resolvedName);
    }
}
