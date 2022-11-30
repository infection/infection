<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser;

interface NodeTraverserInterface
{
    public function addVisitor(NodeVisitor $visitor);
    public function removeVisitor(NodeVisitor $visitor);
    public function traverse(array $nodes) : array;
}
