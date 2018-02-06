<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\Modulus;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class ModulusTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new Modulus();
    }

    public function test_replaces_modulus_with_multiplication()
    {
        $code = '<?php 1 % 2;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

1 * 2;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}
