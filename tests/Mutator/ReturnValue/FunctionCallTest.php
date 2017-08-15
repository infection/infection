<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use Infection\Mutator\ReturnValue\FunctionCall;
use Mutator\ReturnValue\AbstractValueToNullReturnValueTest;


class FunctionCallTest extends AbstractValueToNullReturnValueTest
{
    protected function getMutator() : Mutator
    {
        return new FunctionCall();
    }

    protected function getMutableNodeString(): string
    {
        return 'count([])';
    }
}
