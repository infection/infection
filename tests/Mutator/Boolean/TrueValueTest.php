<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Boolean;

use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;

class TrueValueTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new TrueValue();
    }

    public function test_mutates_true_value()
    {
        $falseValue = new ConstFetch(new Name('true'));

        $this->assertTrue($this->mutator->shouldMutate($falseValue));
    }

    public function test_does_not_mutate_false_value()
    {
        $trueValue = new ConstFetch(new Name('false'));

        $this->assertFalse($this->mutator->shouldMutate($trueValue));
    }

    public function test_replaces_true_with_false()
    {
        $code = '<?php return true;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

return false;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}
