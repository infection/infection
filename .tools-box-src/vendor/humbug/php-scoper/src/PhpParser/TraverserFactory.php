<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceStmtCollection;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\IdentifierResolver;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt\UseStmtCollection;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\PhpScoper;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\EnrichedReflector;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\SymbolsRegistry;
use _HumbugBoxb47773b41c19\PhpParser\NodeTraverser as PhpParserNodeTraverser;
use _HumbugBoxb47773b41c19\PhpParser\NodeTraverserInterface;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitor as PhpParserNodeVisitor;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitor\NameResolver;
class TraverserFactory
{
    public function __construct(private readonly EnrichedReflector $reflector, private readonly string $prefix, private readonly SymbolsRegistry $symbolsRegistry)
    {
    }
    public function create(PhpScoper $scoper) : NodeTraverserInterface
    {
        return self::createTraverser(self::createNodeVisitors($this->prefix, $this->reflector, $scoper, $this->symbolsRegistry));
    }
    private static function createTraverser(array $nodeVisitors) : NodeTraverserInterface
    {
        $traverser = new NodeTraverser(new PhpParserNodeTraverser());
        foreach ($nodeVisitors as $nodeVisitor) {
            $traverser->addVisitor($nodeVisitor);
        }
        return $traverser;
    }
    private static function createNodeVisitors(string $prefix, EnrichedReflector $reflector, PhpScoper $scoper, SymbolsRegistry $symbolsRegistry) : array
    {
        $namespaceStatements = new NamespaceStmtCollection();
        $useStatements = new UseStmtCollection();
        $nameResolver = new NameResolver(null, ['preserveOriginalNames' => \true]);
        $identifierResolver = new IdentifierResolver($nameResolver);
        $stringNodePrefixer = new StringNodePrefixer($scoper);
        return [$nameResolver, new NodeVisitor\AttributeAppender\ParentNodeAppender(), new NodeVisitor\AttributeAppender\IdentifierNameAppender($identifierResolver), new NodeVisitor\NamespaceStmt\NamespaceStmtPrefixer($prefix, $reflector, $namespaceStatements), new NodeVisitor\UseStmt\UseStmtCollector($namespaceStatements, $useStatements), new NodeVisitor\UseStmt\UseStmtPrefixer($prefix, $reflector), new NodeVisitor\FunctionIdentifierRecorder($prefix, $identifierResolver, $symbolsRegistry, $reflector), new NodeVisitor\ClassIdentifierRecorder($prefix, $identifierResolver, $symbolsRegistry, $reflector), new NodeVisitor\NameStmtPrefixer($prefix, $namespaceStatements, $useStatements, $reflector), new NodeVisitor\StringScalarPrefixer($prefix, $reflector), new NodeVisitor\NewdocPrefixer($stringNodePrefixer), new NodeVisitor\EvalPrefixer($stringNodePrefixer), new NodeVisitor\ClassAliasStmtAppender($identifierResolver, $symbolsRegistry), new NodeVisitor\MultiConstStmtReplacer(), new NodeVisitor\ConstStmtReplacer($identifierResolver, $reflector)];
    }
}
