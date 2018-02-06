<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\Increment;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class IncrementTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new Increment();
    }

    public function test_replaces_post_decrement()
    {
        $code = '<?php $a = 1; $a++;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

$a = 1;
$a--;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_replaces_pre_decrement()
    {
        $code = '<?php $a = 1; ++$a;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

$a = 1;
--$a;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_parses_decrement_correctly()
    {
        $code = '<?php 1 + +1;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

1 + +1;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}
