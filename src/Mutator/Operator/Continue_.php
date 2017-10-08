<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Mutator\Operator;

use Infection\Mutator\FunctionBodyMutator;
use PhpParser\Node;

class Continue_ extends FunctionBodyMutator
{
    public function mutate(Node $node)
    {
        return new Node\Stmt\Break_();
    }

    public function shouldMutate(Node $node): bool
    {
        return $node instanceof Node\Stmt\Continue_;
    }
}