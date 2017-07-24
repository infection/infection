<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Tests\Mutator\Boolean;

use Infection\Mutator\Boolean\LogicalAnd;
use Infection\Mutator\Boolean\LogicalLowerAnd;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;

class LogicalLowerAndTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new LogicalLowerAnd();
    }

    public function test_replaces_logical_lower_and_with_or()
    {
        $code = '<?php true and false;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

true or false;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}
