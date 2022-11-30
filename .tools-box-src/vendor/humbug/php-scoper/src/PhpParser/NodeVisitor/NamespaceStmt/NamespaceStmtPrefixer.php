<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\EnrichedReflector;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Namespace_;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
final class NamespaceStmtPrefixer extends NodeVisitorAbstract
{
    public function __construct(private readonly string $prefix, private readonly EnrichedReflector $enrichedReflector, private readonly NamespaceStmtCollection $namespaceStatements)
    {
    }
    public function enterNode(Node $node) : Node
    {
        return $node instanceof Namespace_ ? $this->prefixNamespaceStmt($node) : $node;
    }
    private function prefixNamespaceStmt(Namespace_ $namespace) : Node
    {
        if ($this->shouldPrefixStmt($namespace)) {
            self::prefixStmt($namespace, $this->prefix);
        }
        $this->namespaceStatements->add($namespace);
        return $namespace;
    }
    private function shouldPrefixStmt(Namespace_ $namespace) : bool
    {
        $name = $namespace->name;
        if ($this->enrichedReflector->isExcludedNamespace((string) $name)) {
            return \false;
        }
        $nameFirstPart = null === $name ? '' : $name->getFirst();
        return $this->prefix !== $nameFirstPart;
    }
    private static function prefixStmt(Namespace_ $namespace, string $prefix) : void
    {
        $originalName = $namespace->name;
        $namespace->name = Name::concat($prefix, $originalName);
        NamespaceManipulator::setOriginalName($namespace, $originalName);
    }
}
