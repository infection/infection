<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\FunctionSignature;

use Infection\Mutator\FunctionSignature\ProtectedVisibility;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class ProtectedVisibilityTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new ProtectedVisibility();
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
            'It mutates protected to private' => [
                <<<'PHP'
<?php


class Test
{
    protected function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
PHP
                ,
                <<<'PHP'
<?php

class Test
{
    private function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
PHP
                ,
            ],
            'It does not mutate final flag' => [
                <<<'PHP'
<?php

class Test
{
    protected final function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
PHP
                ,
                <<<'PHP'
<?php

class Test
{
    private final function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
PHP
                ,
            ],
            'It does not mutate abstract protected to private' => [
                <<<'PHP'
<?php

abstract class Test
{
    protected abstract function foo(int $param, $test = 1) : bool;
}
PHP
                ,
            ],
            'It does mutate not abstract protected to private in an abstract class' => [
        <<<'PHP'
<?php

abstract class Test
{
    protected function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
PHP
                ,
                <<<'PHP'
<?php

abstract class Test
{
    private function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
PHP
                ,
            ],
            'It does not mutate stratic flag' => [
                <<<'PHP'
<?php

class Test
{
    protected static function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
PHP
                ,
                <<<'PHP'
<?php

class Test
{
    private static function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
PHP
                ,
            ],
        ];
    }
}
