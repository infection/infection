<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\IgnoreNode;

use _HumbugBox9658796bb9f0\PhpParser\Node;
interface NodeIgnorer
{
    public function ignores(Node $node) : bool;
}
