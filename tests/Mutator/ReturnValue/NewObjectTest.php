<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use Infection\Mutator\ReturnValue\FloatNegation;
use Infection\Mutator\ReturnValue\IntegerNegation;
use Infection\Mutator\ReturnValue\NewObject;
use Infection\Tests\Mutator\AbstractMutator;
use Mutator\ReturnValue\AbstractValueToNullReturnValueTest;


class NewObjectTest extends AbstractValueToNullReturnValueTest
{
    public function test_mutates_instantiation_of_new_object_with_params()
    {
        $code = <<<'CODE'
<?php
function test()
{
    return new Foo('now');
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

function test()
{
    new Foo('now');
    return null;
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    protected function getMutator() : Mutator
    {
        return new NewObject();
    }

    protected function getMutableNodeString(): string
    {
        return 'new Foo()';
    }
}
