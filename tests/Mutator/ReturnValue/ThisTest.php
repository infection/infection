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
                <<<'PHP'
<?php

class Test
{
    function test()
    {
        return $this;
    }
}
PHP
                ,
                <<<'PHP'
<?php

class Test
{
    function test()
    {
        return null;
    }
}
PHP
                ,
            ],
            'It does not mutate return this with typehint' => [
                <<<'PHP'
<?php

class Test
{
    function test() : self
    {
        return $this;
    }
}
PHP
                ,
            ],
            'It does not mutate other returns' => [
                <<<'PHP'
<?php

class Test
{
    function test() : self
    {
        $val = 3;
        return $val;
    }
}
PHP
            ],
            'It does not mutate non return' => [
                <<<'PHP'
<?php

class Test
{
    function test()
    {
        $val = 3;
        $this;
    }
}
PHP
            ],
            'It does not mutate print' => [
                <<<'PHP'
<?php

class Test
{
    function test()
    {
        $val = 3;
        print $this;
    }
}
PHP
            ],
        ];
    }
}
