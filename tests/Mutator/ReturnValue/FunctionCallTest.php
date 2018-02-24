<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use Infection\Mutator\ReturnValue\FunctionCall;

class FunctionCallTest extends AbstractValueToNullReturnValueTestCase
{
    protected function getMutator(): Mutator
    {
        return new FunctionCall();
    }

    protected function getMutableNodeString(): string
    {
        return 'count([])';
    }
}
