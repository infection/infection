<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser;

interface NodeVisitor
{
    public function beforeTraverse(array $nodes);
    public function enterNode(Node $node);
    public function leaveNode(Node $node);
    public function afterTraverse(array $nodes);
}
