<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

abstract class AbstractValueToNullReturnValueTestCase extends AbstractMutatorTestCase
{
    abstract protected function getMutableNodeString(): string;

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
            'It mutates without typehint' => [
                <<<"PHP"
<?php

class Test
{
    function test()
    {
        return {$this->getMutableNodeString()};
    }
}
PHP
                ,
                <<<"PHP"
<?php

class Test
{
    function test()
    {
        {$this->getMutableNodeString()};
        return null;
    }
}
PHP
                ,
            ],
            'It does not mutate when scalar typehint does not allow null' => [
                <<<"PHP"
<?php

class Test
{
    function test() : int
    {
        return {$this->getMutableNodeString()};
    }
}
PHP
                ,
            ],
            'It mutates when scalar typehint allows null' => [
                <<<"PHP"
<?php

class Test
{
    function test() : ?int
    {
        return {$this->getMutableNodeString()};
    }
}
PHP
                ,
                <<<"PHP"
<?php

class Test
{
    function test() : ?int
    {
        {$this->getMutableNodeString()};
        return null;
    }
}
PHP
                ,
            ],
            'It does not mutate when FQN typehint does not allow null' => [
                <<<"PHP"
<?php

class Test
{
    function test() : \DateTime
    {
        return {$this->getMutableNodeString()};
    }
}
PHP
                ,
            ],
            'It mutates when FQL typehint allows null' => [
                <<<"PHP"
<?php

class Test
{
    function test() : ?\DateTime
    {
        return {$this->getMutableNodeString()};
    }
}
PHP
                ,
                <<<"PHP"
<?php

class Test
{
    function test() : ?\DateTime
    {
        {$this->getMutableNodeString()};
        return null;
    }
}
PHP
                ,
            ],
            'It does not mutate return of a function outside of a class' => [
                <<<"PHP"
<?php

function test()
{
    {$this->getMutableNodeString()};
}
PHP
            ],
        ];
    }

    public function test_it_does_not_mutate_a_function_outside_a_class()
    {
        $code = <<<"PHP"
<?php

function test()
{
    return 1;
}
PHP;

        $mutatedCode = $this->mutate($code);
        $this->assertSame($code, $mutatedCode);
    }
}
