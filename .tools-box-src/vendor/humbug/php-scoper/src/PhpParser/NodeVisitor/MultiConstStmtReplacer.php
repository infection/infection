<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor;

use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\ConstFetch;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Const_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\If_;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
use function array_map;
use function count;
final class MultiConstStmtReplacer extends NodeVisitorAbstract
{
    public function enterNode(Node $node) : Node
    {
        if (!$node instanceof Const_) {
            return $node;
        }
        if (count($node->consts) <= 1) {
            return $node;
        }
        $newStatements = array_map(static function (Node\Const_ $const) use($node) : Const_ {
            $newConstNode = clone $node;
            $newConstNode->consts = [$const];
            return $newConstNode;
        }, $node->consts);
        return new If_(new ConstFetch(new Name('true')), ['stmts' => $newStatements]);
    }
}
