<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Regex;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

final class PregQuote extends Mutator
{
    public function mutate(Node $node)
    {
        return $node->args[0];
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Expr\FuncCall &&
            !$node->name instanceof Node\Expr\Variable &&
            strtolower((string) $node->name) == 'preg_quote';
    }
}
