<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\BitwiseAnd;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;

class BitwiseAndTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new BitwiseAnd();
    }

    public function test_replaces_bitwise_and_with_or()
    {
        $code = '<?php 1 & 2;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

1 | 2;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}