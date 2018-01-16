<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Number;

use Infection\Mutator\Mutator;
use Infection\Mutator\Number\OneZeroInteger;
use Infection\Tests\Mutator\AbstractMutator;

class OneZeroIntegerTest extends AbstractMutator
{
    public function test_it_mutates_zero_to_one_integer()
    {
        $code = '<?php 10 + 0;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

10 + 1;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_mutates_one_to_zero_integer()
    {
        $code = '<?php 10 + 1;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

10 + 0;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    protected function getMutator(): Mutator
    {
        return new OneZeroInteger();
    }
}
