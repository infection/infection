<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use Infection\Mutator\ReturnValue\NewObject;

class NewObjectTest extends AbstractValueToNullReturnValueTest
{
    public function test_mutates_instantiation_of_new_object_with_params()
    {
        $code = <<<'CODE'
<?php

class Test
{
    function test()
    {
    return new Foo('now');
    }
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

class Test
{
    function test()
    {
        new Foo('now');
        return null;
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_if_no_class_name_found()
    {
        $code = <<<'CODE'
<?php

function test()
{
    $className = 'SimpleClass';
    $instance = new $className();
}
CODE;
        $mutatedCode = $this->mutate($code);

        $this->assertSame($code, $mutatedCode);
    }

    protected function getMutator(): Mutator
    {
        return new NewObject();
    }

    protected function getMutableNodeString(): string
    {
        return 'new Foo()';
    }
}
