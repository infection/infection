<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\Printer;

use _HumbugBoxb47773b41c19\PhpParser\Node;
interface Printer
{
    public function print(array $newStmts, array $oldStmts, array $oldTokens) : string;
}
