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
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class NewObjectTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new NewObject();
    }

    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null)
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): \Generator
    {
        yield 'It does not mutate if no class name found' => [
            <<<'PHP'
<?php

function test()
{
    $className = 'SimpleClass';
    $instance = new $className();
}
PHP
        ];

        yield 'It does not mutate with not nullable return typehint' => [
            $this->getFileContent('no-not-mutates-with-not-nullable-typehint.php'),
        ];

        yield 'It does not mutate return typehint fqcn does not allow null' => [
            $this->getFileContent('no-not-mutates-return-typehint-fqcn-does-not-allow-null.php'),
        ];

        yield 'It mutates without typehint' => [
            $this->getFileContent('no-mutates-without-typehint.php'),
            <<<"PHP"
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
PHP
        ];

        yield 'It does not mutate when scalar return typehint does not allow null' => [
            $this->getFileContent('no-not-mutates-scalar-return-typehint-does-not-allow-null.php'),
        ];
    }

    public function test_it_mutates_when_function_contains_another_function_but_returns_new_instance_and_null_allowed()
    {
        if (\PHP_VERSION_ID < 70100) {
            $this->markTestSkipped('Current PHP version does not support nullable return typehint.');
        }

        $code = $this->getFileContent('no-contains-another-func-and-null-allowed.php');

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

        $code = $this->getFileContent('no-contains-another-func-and-null-is-not-allowed.php');

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

        $code = $this->getFileContent('no-mutates-return-typehint-fqcn-allows-null.php');
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

    public function test_mutates_when_scalar_return_typehint_allows_null()
    {
        if (\PHP_VERSION_ID < 70100) {
            $this->markTestSkipped('Current PHP version does not support nullable return typehint.');
        }

        $code = $this->getFileContent('no-mutates-scalar-return-typehint-allows-null.php');
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

    private function getFileContent(string $file): string
    {
        return file_get_contents(sprintf(__DIR__ . '/../../Fixtures/Autoloaded/NewObject/%s', $file));
    }
}
