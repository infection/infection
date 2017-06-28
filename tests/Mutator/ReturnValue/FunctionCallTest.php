<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use Infection\Mutator\ReturnValue\FunctionCall;
use Infection\Tests\Mutator\AbstractMutator;


class FunctionCallTest extends AbstractMutator
{
    public function test_not_mutates_with_value_return_true()
    {
        $code = '<?php return true;';
        $nodes = $this->getNodes($code);

        $this->assertFalse($this->mutator->shouldMutate($nodes[0]));
    }

    public function test_not_mutates_with_value_return_true_for_new_objects()
    {
        $code = '<?php return new Foo();';
        $nodes = $this->getNodes($code);

        $this->assertFalse($this->mutator->shouldMutate($nodes[0]));
    }

    public function test_mutates_with_value_return_function_call_no_params()
    {
        $code = '<?php return array_filter();';
        $nodes = $this->getNodes($code);

        $this->assertTrue($this->mutator->shouldMutate($nodes[0]));
    }

    public function test_mutates_with_value_return_function_call_with_params()
    {
        $code = '<?php return array_filter([]);';
        $nodes = $this->getNodes($code);

        $this->assertTrue($this->mutator->shouldMutate($nodes[0]));
    }

    public function testGetsMutationSettingReturnValueNullAndPreservingFunctionCall()
    {
        $code = '<?php return array_filter([]);';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

array_filter([]);
return null;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    protected function getMutator() : Mutator
    {
        return new FunctionCall();
    }
}
