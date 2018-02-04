<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Number;

use Infection\Mutator\Mutator;
use Infection\Mutator\Number\IncrementInteger;
use Infection\Mutator\Number\OneZeroInteger;
use Infection\Tests\Mutator\AbstractMutator;

class IncrementIntegerTest extends AbstractMutator
{
    public function test_it_increments_an_integer()
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

if ($foo < 11) {
    echo 'bar';
}
PHP;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    /**
     * Mutator should skip 0 to reduce the number of mutations.
     *
     * @see OneZeroInteger::mutate()
     */
    public function test_it_does_not_increment_zero()
    {
        $code = <<<'PHP'
<?php

if ($foo < 0) {
    echo 'bar';
}
PHP;

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'PHP'
<?php

if ($foo < 0) {
    echo 'bar';
}
PHP;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    protected function getMutator(): Mutator
    {
        return new IncrementInteger();
    }
}
