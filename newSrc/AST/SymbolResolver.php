<?php

declare(strict_types=1);

namespace newSrc\AST;

use newSrc\Trace\Symbol\Symbol;
use PhpParser\Node;

final class SymbolResolver
{
    public function tryToResolve(Node $node): ?Symbol
    {
        // TODO
        return 'Foo::bar()';
    }
}
