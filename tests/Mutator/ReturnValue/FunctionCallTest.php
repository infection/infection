<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
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
    protected function getMutator(): Mutator
    {
        return new FunctionCall();
    }

    public function test_it_does_not_mutate_a_function_outside_a_class()
    {
        $code = <<<"CODE"
<?php

function test()
{
    return 1;
}
CODE;

        $mutatedCode = $this->mutate($code);
        $this->assertSame($code, $mutatedCode);
    }

    public function test_not_mutates_with_not_nullable_return_typehint()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/FunctionCall/fc-not-mutates-with-not-nullable-typehint.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace FunctionCall_NotMutatesWithNotNullableTypehint;

class Test
{
    function test() : bool
    {
        return count([]);
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_mutates_when_function_contains_another_function_but_returns_function_call_and_null_allowed()
    {
        if (\PHP_VERSION_ID < 70100) {
            $this->markTestSkipped('Current PHP version does not support nullable return typehint.');
        }

        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/FunctionCall/fc-contains-another-func-and-null-allowed.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace FunctionCall_ContainsAnotherFunctionAndNullAllowed;

class Test
{
    function test()
    {
        \$a = function (\$element) : ?int {
            return \$element;
        };
        count([]);
        return null;
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_when_function_contains_another_function_but_return_null_is_not_allowed()
    {
        if (\PHP_VERSION_ID < 70100) {
            $this->markTestSkipped('Current PHP version does not support nullable return typehint.');
        }

        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/FunctionCall/fc-contains-another-func-and-null-is-not-allowed.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace FunctionCall_ContainsAnotherFunctionAndNullIsNotAllowed;

class Test
{
    function test() : int
    {
        \$a = function (\$element) : ?int {
            return \$element;
        };
        return count([]);
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_mutates_when_return_typehint_fqcn_allows_null()
    {
        if (\PHP_VERSION_ID < 70100) {
            $this->markTestSkipped('Current PHP version does not support nullable return typehint.');
        }

        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/FunctionCall/fc-mutates-return-typehint-fqcn-allows-null.php');
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace FunctionCall_ReturnTypehintFqcnAllowsNull;

class Test
{
    function test() : ?\DateTime
    {
        count([]);
        return null;
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutates_when_return_typehint_fqcn_does_not_allow_null()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/FunctionCall/fc-not-mutates-return-typehint-fqcn-does-not-allow-null.php');
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace FunctionCall_ReturnTypehintFqcnDoesNotAllowNull;

class Test
{
    function test() : \DateTime
    {
        return count([]);
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_mutates_without_typehint()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/FunctionCall/fc-mutates-without-typehint.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace FunctionCall_MutatesWithoutTypehint;

class Test
{
    function test()
    {
        count([]);
        return null;
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_when_scalar_return_typehint_does_not_allow_null()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/FunctionCall/fc-not-mutates-scalar-return-typehint-does-not-allow-null.php');
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace FunctionCall_ScalarReturnTypehintFqcnDoesNotAllowNull;

class Test
{
    function test() : int
    {
        return count([]);
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_mutates_when_scalar_return_typehint_allows_null()
    {
        if (\PHP_VERSION_ID < 70100) {
            $this->markTestSkipped('Current PHP version does not support nullable return typehint.');
        }

        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/FunctionCall/fc-mutates-scalar-return-typehint-allows-null.php');
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace FunctionCall_ScalarReturnTypehintAllowsNull;

class Test
{
    function test() : ?int
    {
        count([]);
        return null;
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}
