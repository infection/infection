<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser;

interface NodeTraverserInterface
{
    public function addVisitor(NodeVisitor $visitor);
    public function removeVisitor(NodeVisitor $visitor);
    public function traverse(array $nodes) : array;
}
