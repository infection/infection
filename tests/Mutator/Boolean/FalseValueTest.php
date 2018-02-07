<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Boolean;

use Infection\Mutator\Boolean\FalseValue;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;

class FalseValueTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new FalseValue();
    }

    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null)
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): array
    {
        return [
            'It mutates false to true' => [
                <<<'CODE'
<?php

return false;
CODE
                ,
                <<<'CODE'
<?php

return true;
CODE
                ,
            ],
            'It does not mutate the string false to true' => [
                <<<'CODE'
<?php

return 'false';
CODE
                ,
            ],
            'It mutates all caps false to true' => [
                <<<'CODE'
<?php

return FALSE;
CODE
                ,
                <<<'CODE'
<?php

return true;
CODE
                ,
            ],
        ];
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
}
