<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use Infection\Mutator\ReturnValue\IntegerNegation;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class IntegerNegationTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new IntegerNegation();
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
            'It mutates negative int return to positive' => [
                <<<'CODE'
<?php

return -2;
CODE
                ,
                <<<'CODE'
<?php

return 2;
CODE
                ,
            ],
            'It mutates positive int return to negative' => [
                <<<'CODE'
<?php

return 2;
CODE
                ,
                <<<'CODE'
<?php

return -2;
CODE
                ,
            ],
            'It does not mutate int zero' => [
                <<<'CODE'
<?php

return 0;
CODE
                ,
            ],
            'It does not mutate floats' => [
                <<<'CODE'
<?php

return 1.0;
CODE
                ,
            ],
        ];
    }
}
