<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\Exponentiation;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;

class ExponentiationTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new Exponentiation();
    }

    public function test_replaces_multiplication_with_division()
    {
        $code = '<?php 1 ** 2;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

1 / 2;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}