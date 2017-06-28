<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Test\Mutator\Boolean;

use Infection\Mutator\Boolean\LogicalAnd;
use Infection\Mutator\Boolean\LogicalLowerAnd;
use Infection\Mutator\Boolean\LogicalLowerOr;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;

class LogicalLowerOrTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new LogicalLowerOr();
    }

    public function test_replaces_logical_lower_or_with_and()
    {
        $code = '<?php true or false;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

true and false;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}
