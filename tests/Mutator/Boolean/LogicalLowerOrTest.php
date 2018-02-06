<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Boolean;

use Infection\Mutator\Boolean\LogicalLowerOr;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class LogicalLowerOrTest extends AbstractMutatorTestCase
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
