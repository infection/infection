<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Mutator\ReturnValue;

use Infection\Tests\Mutator\AbstractMutator;

abstract class AbstractValueToNullReturnValueTest extends AbstractMutator
{
    public function test_not_mutates_with_value_return_true()
    {
        $code = <<<'CODE'
<?php
function test() : bool
{
    return true;
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

function test() : bool
{
    return true;
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_not_mutates_with_value_return_true_for_new_objects()
    {
        $code = <<<'CODE'
<?php
function test()
{
    return new Foo();
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

function test()
{
    return new Foo();
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}