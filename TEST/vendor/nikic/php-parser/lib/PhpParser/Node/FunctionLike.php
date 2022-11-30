<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node;

use _HumbugBox9658796bb9f0\PhpParser\Node;
interface FunctionLike extends Node
{
    public function returnsByRef() : bool;
    public function getParams() : array;
    public function getReturnType();
    public function getStmts();
    public function getAttrGroups() : array;
}
