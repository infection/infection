<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
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
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/This_/this_return-this.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

namespace This_ReturnThis;

class Test
{
    function test()
    {
        return null;
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    protected function getMutator(): Mutator
    {
        return new This();
    }
}
