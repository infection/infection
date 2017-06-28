<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);


namespace Infection\Tests\Mutator\Boolean;


use Infection\Mutator\Boolean\FalseValue;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;

class FalseValueTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new FalseValue();
    }

    public function test_mutates_false_value()
    {
        $falseValue = new ConstFetch(new Name('false'));

        $this->assertTrue($this->mutator->shouldMutate($falseValue));
    }

    public function test_does_not_mutate_true_value()
    {
        $falseValue = new ConstFetch(new Name('true'));

        $this->assertFalse($this->mutator->shouldMutate($falseValue));
    }

    public function test_replaces_false_with_true()
    {
        $code = '<?php return false;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

return true;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}