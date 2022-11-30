<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceStmtCollection;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Use_;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
final class UseStmtCollector extends NodeVisitorAbstract
{
    public function __construct(private readonly NamespaceStmtCollection $namespaceStatements, private readonly UseStmtCollection $useStatements)
    {
    }
    public function enterNode(Node $node) : Node
    {
        if ($node instanceof Use_) {
            $namespaceName = $this->namespaceStatements->getCurrentNamespaceName();
            $this->useStatements->add($namespaceName, $node);
        }
        return $node;
    }
}
