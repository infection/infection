<?php

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use Infection\Mutator\ReturnValue\IntegerNegation;
use Infection\Tests\Mutator\AbstractMutator;


class IntegerNegationTest extends AbstractMutator
{
    public function test_not_mutates_with_value_return_true()
    {
        $code = '<?php return true;';
        $nodes = $this->getNodes($code);

        $this->assertFalse($this->mutator->shouldMutate($nodes[0]));
    }

    public function test_not_mutates_with_value_zero()
    {
        $code = '<?php return 0;';
        $nodes = $this->getNodes($code);

        $this->assertFalse($this->mutator->shouldMutate($nodes[0]));
    }

    public function test_not_mutates_with_value_float()
    {
        $code = '<?php return 1.0;';
        $nodes = $this->getNodes($code);

        $this->assertFalse($this->mutator->shouldMutate($nodes[0]));
    }

    public function test_not_mutates_with_function_call()
    {
        $code = '<?php return count([]);';
        $nodes = $this->getNodes($code);

        $this->assertFalse($this->mutator->shouldMutate($nodes[0]));
    }

    public function test_not_mutates_with_negated_function_call()
    {
        $code = '<?php return -count([]);';
        $nodes = $this->getNodes($code);

        $this->assertFalse($this->mutator->shouldMutate($nodes[0]));
    }

    public function testMutatesWithValueOne()
    {
        $code = '<?php return 1;';
        $nodes = $this->getNodes($code);

        $this->assertTrue($this->mutator->shouldMutate($nodes[0]));
    }

    public function test_mutates_with_negative_value()
    {
        $code = '<?php return -1;';
        $nodes = $this->getNodes($code);

        $this->assertTrue($this->mutator->shouldMutate($nodes[0]));
    }

    public function test_gets_mutation_reverses_integer_sign_when_positive()
    {
        $code = '<?php return 2;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

return -2;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function testGetsMutationReversesIntegerSignWhenNegative()
    {
        $code = '<?php return -1;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

return 1;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    protected function getMutator() : Mutator
    {
        return new IntegerNegation();
    }
}
