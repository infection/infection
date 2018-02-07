<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Boolean;

use Infection\Mutator\Boolean\LogicalLowerOr;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class LogicalLowerOrTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new LogicalLowerOr();
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
            'It mutates logical lower or' => [
                <<<'CODE'
<?php

true or false;
CODE
                ,
                <<<'CODE'
<?php

true and false;
CODE
                ,
            ],
            'It does not mutate logical or' => [
                <<<'CODE'
<?php

true || false;
CODE
                ,
            ],
        ];
    }
}
