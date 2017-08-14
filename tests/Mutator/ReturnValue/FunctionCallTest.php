<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use Infection\Mutator\ReturnValue\FunctionCall;
use Infection\Tests\Mutator\AbstractMutator;


class FunctionCallTest extends AbstractMutator
{
    public function test_not_mutates_with_value_return_true()
    {
        $code = <<<'CODE'
<?php
function test() : bool
{
    return true;
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

function test() : bool
{
    return true;
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_not_mutates_with_value_return_true_for_new_objects()
    {
        $code = <<<'CODE'
<?php
function test()
{
    return new Foo();
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

function test()
{
    return new Foo();
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_mutates_with_value_return_function_call_no_params()
    {
        $code = <<<'CODE'
<?php

function test()
{
    return array_filter();
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

function test()
{
    array_filter();
    return null;
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_gets_mutation_setting_return_value_null_and_preserving_function_call()
    {
        $code = <<<'CODE'
<?php

function test()
{
    array_filter([]);
    return null;
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

function test()
{
    array_filter([]);
    return null;
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_when_scalar_return_typehint_does_not_allow_null()
    {
        $code = <<<'CODE'
<?php
function test() : int
{
    return count([]);
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

function test() : int
{
    return count([]);
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_mutates_when_scalar_return_typehint_allows_null()
    {
        $code = <<<'CODE'
<?php
function test() : ?int
{
    return count([]);
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

function test() : ?int
{
    count([]);
    return null;
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_when_return_typehint_fqcn_does_not_allow_null()
    {
        $code = <<<'CODE'
<?php
function test() : \DateTime
{
    return count([]);
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

function test() : \DateTime
{
    return count([]);
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_mutates_when_return_typehint_fqcn_allows_null()
    {
        $code = <<<'CODE'
<?php
function test() : ?\DateTime
{
    return count([]);
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

function test() : ?\DateTime
{
    count([]);
    return null;
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_when_function_contains_another_function_but_returns_function_call()
    {
        $code = <<<'CODE'
<?php
function test() : array
{
    $a = function ($element) : ?int {
        return $element;
    };
    
    return array_map($a, [1]);
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

function test() : array
{
    $a = function ($element) : ?int {
        return $element;
    };
    return array_map($a, [1]);
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_mutates_when_function_contains_another_function_but_returns_function_call_and_null_allowed()
    {
        $code = <<<'CODE'
<?php
function test()
{
    $a = function ($element) : ?int {
        return $element;
    };
    
    return array_map($a, [1]);
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

function test()
{
    $a = function ($element) : ?int {
        return $element;
    };
    array_map($a, [1]);
    return null;
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    protected function getMutator() : Mutator
    {
        return new FunctionCall();
    }
}
