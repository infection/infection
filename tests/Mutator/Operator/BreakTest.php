<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Operator;

use Infection\Mutator\Operator\Break_;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;

class BreakTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new Break_();
    }

    public function test_replace_break_to_continue()
    {
        $code = <<<'CODE'
<?php
while (true) {
    break;
}
CODE;

        $expectedCode = <<<'CODE'
<?php

while (true) {
    continue;
}
CODE;

        $this->assertSame($expectedCode, $this->mutate($code));
    }

    public function test_does_not_replace_break_to_continue_in_switch()
    {
        $code = <<<'CODE'
<?php

switch (1) {
    case 1:
        break;
}
CODE;

        $expectedCode = <<<'CODE'
<?php

switch (1) {
    case 1:
        break;
}
CODE;

        $this->assertSame($expectedCode, $this->mutate($code));
    }
}
