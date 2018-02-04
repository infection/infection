<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Operator;

use Infection\Mutator\Mutator;
use Infection\Mutator\Operator\Continue_;
use Infection\Tests\Mutator\AbstractMutator;

class ContinueTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new Continue_();
    }

    public function test_replace_continue_to_break()
    {
        $code = <<<'CODE'
<?php
while (true) {
    continue;
}
CODE;

        $expectedCode = <<<'CODE'
<?php

while (true) {
    break;
}
CODE;

        $this->assertSame($expectedCode, $this->mutate($code));
    }

    public function test_does_not_replace_continue_to_break_in_switch()
    {
        $code = <<<'CODE'
<?php

switch (1) {
    case 1:
        continue;
}
CODE;

        $expectedCode = <<<'CODE'
<?php

switch (1) {
    case 1:
        continue;
}
CODE;

        $this->assertSame($expectedCode, $this->mutate($code));
    }
}
