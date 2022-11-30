<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt;

use ArrayIterator;
use Countable;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\AttributeAppender\ParentNodeAppender;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\UnexpectedParsingScenario;
use IteratorAggregate;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Namespace_;
use Traversable;
use function count;
use function end;
final class NamespaceStmtCollection implements IteratorAggregate, Countable
{
    private array $nodes = [];
    private array $mapping = [];
    public function add(Namespace_ $namespace) : void
    {
        $this->nodes[] = $namespace;
        $this->mapping[(string) $namespace->name] = NamespaceManipulator::getOriginalName($namespace);
    }
    public function findNamespaceForNode(Node $node) : ?Name
    {
        if (0 === count($this->nodes)) {
            return null;
        }
        if (1 === count($this->nodes)) {
            return NamespaceManipulator::getOriginalName($this->nodes[0]);
        }
        return $this->getNodeNamespaceName($node);
    }
    public function getCurrentNamespaceName() : ?Name
    {
        $lastNode = end($this->nodes);
        return \false === $lastNode ? null : NamespaceManipulator::getOriginalName($lastNode);
    }
    public function count() : int
    {
        return count($this->nodes);
    }
    private function getNodeNamespaceName(Node $node) : ?Name
    {
        if (!ParentNodeAppender::hasParent($node)) {
            throw UnexpectedParsingScenario::create();
        }
        $parentNode = ParentNodeAppender::getParent($node);
        if ($parentNode instanceof Namespace_) {
            return $this->mapping[(string) $parentNode->name];
        }
        return $this->getNodeNamespaceName($parentNode);
    }
    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->nodes);
    }
}
