<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Generator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @group integration Requires some I/O operations
 */
final class NewObjectTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider mutationsProvider
     *
     * @param string|string[] $expected
     */
    public function test_it_can_mutate($input, $expected = [], bool $allowed = true, $message = ''): void
    {
        if (!$allowed) {
            $this->markTestSkipped($message);
        }
        $this->doTest($input, $expected);
    }

    public function mutationsProvider(): Generator
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

use stdClass;
class Test
{
    function test()
    {
        new stdClass();
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

use stdClass;
class Test
{
    function test()
    {
        \$a = function (\$element) : ?stdClass {
            return \$element;
        };
        new stdClass();
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

use stdClass;
class Test
{
    function test() : ?stdClass
    {
        new stdClass();
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

use stdClass;
class Test
{
    function test() : ?int
    {
        new stdClass();
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
