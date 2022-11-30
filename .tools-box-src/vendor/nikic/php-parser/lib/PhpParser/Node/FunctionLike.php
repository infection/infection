<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node;

use _HumbugBoxb47773b41c19\PhpParser\Node;
interface FunctionLike extends Node
{
    public function returnsByRef() : bool;
    public function getParams() : array;
    public function getReturnType();
    public function getStmts();
    public function getAttrGroups() : array;
}
