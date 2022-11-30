<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\Node\NameFactory;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Declare_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\GroupUse;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\InlineHTML;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Namespace_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Use_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\UseUse;
use _HumbugBoxb47773b41c19\PhpParser\NodeTraverserInterface;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitor;
use function array_map;
use function array_slice;
use function array_splice;
use function array_values;
use function count;
use function current;
final class NodeTraverser implements NodeTraverserInterface
{
    public function __construct(private readonly NodeTraverserInterface $decoratedTraverser)
    {
    }
    public function addVisitor(NodeVisitor $visitor) : void
    {
        $this->decoratedTraverser->addVisitor($visitor);
    }
    public function removeVisitor(NodeVisitor $visitor) : void
    {
        $this->decoratedTraverser->removeVisitor($visitor);
    }
    public function traverse(array $nodes) : array
    {
        $nodes = $this->wrapInNamespace($nodes);
        $nodes = $this->replaceGroupUseStatements($nodes);
        return $this->decoratedTraverser->traverse($nodes);
    }
    private function wrapInNamespace(array $nodes) : array
    {
        if ([] === $nodes) {
            return $nodes;
        }
        $nodes = array_values($nodes);
        $firstRealStatementIndex = 0;
        $realStatements = [];
        foreach ($nodes as $i => $node) {
            if ($node instanceof Declare_ || $node instanceof InlineHTML) {
                continue;
            }
            $firstRealStatementIndex = $i;
            $realStatements = array_slice($nodes, $i);
            break;
        }
        $firstRealStatement = current($realStatements);
        if (\false !== $firstRealStatement && !$firstRealStatement instanceof Namespace_) {
            $wrappedStatements = new Namespace_(null, $realStatements);
            array_splice($nodes, $firstRealStatementIndex, count($realStatements), [$wrappedStatements]);
        }
        return $nodes;
    }
    private function replaceGroupUseStatements(array $nodes) : array
    {
        foreach ($nodes as $node) {
            if (!$node instanceof Namespace_) {
                continue;
            }
            $statements = $node->stmts;
            $newStatements = [];
            foreach ($statements as $statement) {
                if ($statement instanceof GroupUse) {
                    $uses_ = $this->createUses_($statement);
                    array_splice($newStatements, count($newStatements), 0, $uses_);
                } else {
                    $newStatements[] = $statement;
                }
            }
            $node->stmts = $newStatements;
        }
        return $nodes;
    }
    private function createUses_(GroupUse $node) : array
    {
        return array_map(static function (UseUse $use) use($node) : Use_ {
            $newUse = new UseUse(NameFactory::concat($node->prefix, $use->name, $use->name->getAttributes()), $use->alias, $use->type, $use->getAttributes());
            return new Use_([$newUse], $node->type, $node->getAttributes());
        }, $node->uses);
    }
}
