<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Number;

use Infection\Mutator\Mutator;
use Infection\Mutator\Number\DecrementInteger;
use Infection\Mutator\Number\OneZeroInteger;
use Infection\Tests\Mutator\AbstractMutator;

class DecrementIntegerTest extends AbstractMutator
{
    public function test_it_decrements_an_integer()
    {
        $code = <<<'PHP'
<?php

if ($foo < 10) {
    echo 'bar';
}
PHP;

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'PHP'
<?php

if ($foo < 9) {
    echo 'bar';
}
PHP;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    /**
     * Mutator should skip 1 to reduce the number of mutations.
     *
     * @see OneZeroInteger::mutate()
     */
    public function test_it_does_not_decrement_one()
    {
        $code = <<<'PHP'
<?php

if ($foo < 1) {
    echo 'bar';
}
PHP;

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'PHP'
<?php

if ($foo < 1) {
    echo 'bar';
}
PHP;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    protected function getMutator(): Mutator
    {
        return new DecrementInteger();
    }
}
