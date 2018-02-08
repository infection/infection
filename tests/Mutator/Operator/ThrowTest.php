<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Operator;

use Infection\Mutator\Mutator;
use Infection\Mutator\Operator\Throw_;
use Infection\Tests\Mutator\AbstractMutator;

class ThrowTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new Throw_();
    }

    public function test_replace_break_to_continue()
    {
        $code = <<<'CODE'
<?php

throw new \Exception();
CODE;

        $expectedCode = <<<'CODE'
<?php

new \Exception();
CODE;

        $this->assertSame($expectedCode, $this->mutate($code));
    }
}
