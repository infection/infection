<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\FunctionSignature;

use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class PublicVisibilityTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider blacklistedProvider
     */
    public function test_it_does_not_modify_blacklisted_functions(string $functionName)
    {
        $code = <<<"CODE"
<?php

class Test
{
    public function {$functionName}()
    {
    }
}
CODE;
        $this->doTest($code);
    }

    public function blacklistedProvider()
    {
        return [
            ['__construct'],
            ['__invoke'],
            ['__call'],
            ['__callStatic'],
            ['__get'],
            ['__set'],
            ['__isset'],
            ['__unset'],
            ['__toString'],
            ['__debugInfo'],
        ];
    }

    protected function getMutator(): Mutator
    {
        return new PublicVisibility();
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
            'It mutates public to protected' => [
                <<<'CODE'
<?php


class Test
{
    public function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
CODE
                ,
                <<<'CODE'
<?php

class Test
{
    protected function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
CODE
                ,
            ],
            'It does not mutate final flag' => [
                <<<'CODE'
<?php

class Test
{
    public final function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
CODE
                ,
                <<<'CODE'
<?php

class Test
{
    protected final function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
CODE
                ,
            ],
            'It mutates non abstract public to protected in an abstract class' => [
                <<<'CODE'
<?php

abstract class Test
{
    public function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
CODE
                ,
                <<<'CODE'
<?php

abstract class Test
{
    protected function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
CODE
                ,
            ],
            'It does not mutate static flag' => [
                <<<'CODE'
<?php

class Test
{
    public static function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
CODE
                ,
                <<<'CODE'
<?php

class Test
{
    protected static function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
CODE
                ,
            ],
            'It replaces visibility if not set' => [
                <<<'CODE'
<?php

class Test
{
    function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
CODE
                ,
                <<<'CODE'
<?php

class Test
{
    protected function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
CODE
                ,
            ],
            'It does not mutate an interface' => [
                <<<'CODE'
<?php

interface TestInterface
{
    public function test();
}
CODE
            ],
        ];
    }
}
