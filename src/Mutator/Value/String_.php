<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Mutator\Value;

use Infection\Mutator\Mutator;
use PhpParser\Node;

class String_ extends Mutator
{
    public function mutate(Node $node)
    {
        return new Node\Scalar\String_('');
    }

    public function shouldMutate(Node $node): bool
    {
        return
            $node instanceof Node\Scalar\String_ &&
            $node->value !== '';
    }
}
