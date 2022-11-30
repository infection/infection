<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\IgnoreNode;

use _HumbugBox9658796bb9f0\PhpParser\Node;
class ChangingIgnorer implements NodeIgnorer
{
    private bool $ignore = \false;
    public function ignores(Node $node) : bool
    {
        return $this->ignore;
    }
    public function startIgnoring() : void
    {
        $this->ignore = \true;
    }
    public function stopIgnoring() : void
    {
        $this->ignore = \false;
    }
}
