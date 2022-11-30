<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework;

use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\IgnoreNode\NodeIgnorer;
interface IgnoresAdditionalNodes
{
    public function getNodeIgnorers() : array;
}
