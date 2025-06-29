<?php

namespace newSrc\AST;

use PhpParser\Node;

interface AridCodeDetector
{
    public function isArid(Node $node): bool;
}
