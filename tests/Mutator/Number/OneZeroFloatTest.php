<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Number;

use Infection\Mutator\Mutator;
use Infection\Mutator\Number\OneZeroFloat;
use Infection\Tests\Mutator\AbstractMutator;

class OneZeroFloatTest extends AbstractMutator
{
    public function test_it_mutates_zero_to_one_float()
    {
        $code = '<?php 10 + 0.0;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

10 + 1.0;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_mutates_one_to_zero_float()
    {
        $code = '<?php 10 + 1.0;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

10 + 0.0;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    protected function getMutator(): Mutator
    {
        return new OneZeroFloat();
    }
}
