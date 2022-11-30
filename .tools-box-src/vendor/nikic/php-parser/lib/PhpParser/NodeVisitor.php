<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser;

interface NodeVisitor
{
    public function beforeTraverse(array $nodes);
    public function enterNode(Node $node);
    public function leaveNode(Node $node);
    public function afterTraverse(array $nodes);
}
