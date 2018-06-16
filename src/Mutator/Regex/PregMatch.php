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
use PhpParser\Node\Expr\FuncCall;

/**
 * @internal
 */
abstract class PregMatch extends Mutator
{
    /**
     * Replaces "preg_match"
     *
     * @param Node $node
     *
     * @return mixed
     */
    public function mutate(Node $node)
    {
        $arguments = $node->args;
        $pattern = $this->pullOutPattern($arguments[0]);
        $arguments[0] = $this->setNewPattern($this->manipulatePattern($pattern), $arguments[0]);
        return new FuncCall($node->name, $arguments, $node->getAttributes());
    }

    protected function mutatesNode(Node $node): bool
    {
        return $node instanceof FuncCall &&
            $node->name instanceof Node\Name &&
            strtolower((string) $node->name) == 'preg_match';
    }

    protected function pullOutPattern(Node\Arg $argument) : string
    {
        /** @var Node\Scalar\String_ $stringNode */
        $stringNode = $argument->value;

        return $stringNode->value;
    }

    abstract protected function manipulatePattern(string $pattern): string;

    protected function setNewPattern(string $pattern, Node\Arg $argument): Node\Arg
    {
        /** @var Node\Scalar\String_ $stringNode */
        $stringNode = $argument->value;
        $stringNode->value = $pattern;

        return $argument;
    }
}
