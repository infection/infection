<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class NewObjectTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null, bool $allowed = true, $message = ''): void
    {
        if (!$allowed) {
            $this->markTestSkipped($message);
        }
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

        yield 'It mutates when function contains another function but returns new instance and null allowed' => [
            $this->getFileContent('no-contains-another-func-and-null-allowed.php'),
            <<<"CODE"
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
CODE
            ,
        ];

        yield 'It does not mutate when function contains another function but return null is not allowed' => [
            $this->getFileContent('no-contains-another-func-and-null-is-not-allowed.php'),
            null,
        ];

        yield 'It mutates when return typehint fqcn allows null' => [
            $this->getFileContent('no-mutates-return-typehint-fqcn-allows-null.php'),
            <<<"CODE"
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
CODE
            ,
        ];

        yield 'It mutates when scalar return typehint allows null' => [
            $this->getFileContent('no-mutates-scalar-return-typehint-allows-null.php'),
            <<<"CODE"
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
CODE
            ,
        ];

        yield 'It does not mutate the return of an anonymous class' => [
            $this->getFileContent('no-not-mutates-anonymous-class.php'),
        ];
    }

    private function getFileContent(string $file): string
    {
        return file_get_contents(sprintf(__DIR__ . '/../../Fixtures/Autoloaded/NewObject/%s', $file));
    }
}
