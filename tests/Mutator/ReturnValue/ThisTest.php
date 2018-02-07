<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use Infection\Mutator\ReturnValue\This;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class ThisTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new This();
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
            'It mutates return this without typehint' => [
                <<<'CODE'
<?php

class Test
{
    function test()
    {
        return $this;
    }
}
CODE
                ,
                <<<'CODE'
<?php

class Test
{
    function test()
    {
        return null;
    }
}
CODE
                ,
            ],
            'It does not mutate return this with typehint' => [
                <<<'CODE'
<?php

class Test
{
    function test() : self
    {
        return $this;
    }
}
CODE
                ,
            ],
        ];
    }
}
