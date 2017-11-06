<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use Infection\Mutator\ReturnValue\NewObject;
use Infection\Tests\Mutator\AbstractMutator;

class NewObjectTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new NewObject();
    }

    public function test_it_does_not_mutate_if_no_class_name_found()
    {
        $code = <<<'CODE'
<?php

function test()
{
    $className = 'SimpleClass';
    $instance = new $className();
}
CODE;
        $mutatedCode = $this->mutate($code);

        $this->assertSame($code, $mutatedCode);
    }

    public function test_not_mutates_with_not_nullable_return_typehint()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/NewObject/no-not-mutates-with-not-nullable-typehint.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace NewObject_NotMutatesWithNotNullableTypehint;

class Test
{
    function test() : \stdClass
    {
        return new \stdClass();
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_mutates_when_function_contains_another_function_but_returns_new_instance_and_null_allowed()
    {
        if (\PHP_VERSION_ID < 70100) {
            $this->markTestSkipped('Current PHP version does not support nullable return typehint.');
        }

        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/NewObject/no-contains-another-func-and-null-allowed.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace NewObject_ContainsAnotherFunctionAndNullAllowed;

class Test
{
    function test()
    {
        \$a = function (\$element) : ?\stdClass {
            return \$element;
        };
        new \stdClass();
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

        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/NewObject/no-contains-another-func-and-null-is-not-allowed.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace NewObject_ContainsAnotherFunctionAndNullIsNotAllowed;

class Test
{
    function test() : \stdClass
    {
        \$a = function (\$element) : ?\stdClass {
            return \$element;
        };
        return new \stdClass();
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

        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/NewObject/no-mutates-return-typehint-fqcn-allows-null.php');
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace NewObject_ReturnTypehintFqcnAllowsNull;

class Test
{
    function test() : ?\stdClass
    {
        new \stdClass();
        return null;
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutates_when_return_typehint_fqcn_does_not_allow_null()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/NewObject/no-not-mutates-return-typehint-fqcn-does-not-allow-null.php');
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace NewObject_ReturnTypehintFqcnDoesNotAllowNull;

class Test
{
    function test() : \stdClass
    {
        return new \stdClass();
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_mutates_without_typehint()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/NewObject/no-mutates-without-typehint.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace NewObject_MutatesWithoutTypehint;

class Test
{
    function test()
    {
        new \stdClass();
        return null;
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_when_scalar_return_typehint_does_not_allow_null()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/NewObject/no-not-mutates-scalar-return-typehint-does-not-allow-null.php');
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace NewObject_ScalarReturnTypehintFqcnDoesNotAllowNull;

class Test
{
    function test() : int
    {
        return new \stdClass();
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_mutates_when_scalar_return_typehint_allows_null()
    {
        if (\PHP_VERSION_ID < 70100) {
            $this->markTestSkipped('Current PHP version does not support nullable return typehint.');
        }

        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/NewObject/no-mutates-scalar-return-typehint-allows-null.php');
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace NewObject_ScalarReturnTypehintsAllowsNull;

class Test
{
    function test() : ?int
    {
        new \stdClass();
        return null;
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}
