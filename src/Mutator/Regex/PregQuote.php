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

/**
 * @internal
 */
final class PregQuote extends Mutator
{
    /**
     * Replaces "$a = preg_quote($b);" with "$a = $b;"
     *
     * @param Node $node
     *
     * @return iterable
     */
    public function mutate(Node $node): iterable
    {
        yield $node->args[0];
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof Node\Expr\FuncCall &&
            $node->name instanceof Node\Name &&
            strtolower((string) $node->name) === 'preg_quote';
    }
}
