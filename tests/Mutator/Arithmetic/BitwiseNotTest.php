<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\BitwiseNot;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;

class BitwiseNotTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new BitwiseNot();
    }

    public function test_replaces_bitwise_not_with_empty_string()
    {
        $code = '<?php ~2;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

2;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}
