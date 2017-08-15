<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use Infection\Mutator\ReturnValue\This;
use Infection\Tests\Mutator\AbstractMutator;

class ThisTest extends AbstractMutator
{
    public function test_mutates_returning_this()
    {
        $code = <<<'CODE'
<?php
function test()
{
    return $this;
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

function test()
{
    return null;
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    protected function getMutator() : Mutator
    {
        return new This();
    }
}
