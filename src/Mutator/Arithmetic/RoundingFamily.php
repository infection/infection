<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Arithmetic;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
final class RoundingFamily extends Mutator
{
    private const MUTATORS_MAP = [
        'floor',
        'ceil',
        'round',
    ];

    /**
     * Mutates from one rounding function to all others:
     *     1. floor() to ceil() and round()
     *     2. ceil() to floor() and round()
     *     3. round() to ceil() and round()
     *
     * @param Node\Expr\FuncCall|Node $node
     *
     * @return \Generator
     */
    public function mutate(Node $node): \Generator
    {
        $currentFunctionName = $this->getNormalizedFunctionName($node->name);

        $mutateToFunctions = array_diff(self::MUTATORS_MAP, [$currentFunctionName]);

        foreach ($mutateToFunctions as $functionName) {
            yield new Node\Expr\FuncCall(
                new Node\Name($functionName),
                [$node->args[0]],
                $node->getAttributes()
            );
        }
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Expr\FuncCall) {
            return false;
        }

        if (!$node->name instanceof Node\Name ||
            !\in_array($this->getNormalizedFunctionName($node->name), self::MUTATORS_MAP, true)) {
            return false;
        }

        return true;
    }

    private function getNormalizedFunctionName(Node\Name $name): string
    {
        return strtolower((string) $name);
    }
}
