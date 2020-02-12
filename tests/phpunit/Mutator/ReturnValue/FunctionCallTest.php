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
use function Safe\file_get_contents;
use function Safe\sprintf;

/**
 * @group integration Requires some I/O operations
 */
final class FunctionCallTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider mutationsProvider
     *
     * @param string|string[] $expected
     */
    public function test_it_can_mutate(string $input, $expected = []): void
    {
        $this->doTest($input, $expected);
    }

    public function mutationsProvider(): Generator
    {
        yield 'It does not mutate with not nullable return typehint' => [
            $this->getFileContent('fc-not-mutates-with-not-nullable-typehint.php'),
        ];

        yield 'It does not mutates when return typehint FQCN does not allow null' => [
            $this->getFileContent('fc-not-mutates-return-typehint-fqcn-does-not-allow-null.php'),
        ];

        yield 'It mutates without typehint' => [
            $this->getFileContent('fc-mutates-without-typehint.php'),
            <<<"PHP"
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
PHP
        ];

        yield 'It does not mutate when scalar return typehint does not allow null' => [
            $this->getFileContent('fc-not-mutates-scalar-return-typehint-does-not-allow-null.php'),
        ];
    }

    public function test_it_does_not_mutate_when_function_contains_another_function_but_return_null_is_not_allowed(): void
    {
        $code = $this->getFileContent('fc-contains-another-func-and-null-is-not-allowed.php');

        $mutations = $this->mutate($code);

        $this->assertCount(0, $mutations);
    }

    public function test_it_mutates_when_return_typehint_fqcn_allows_null(): void
    {
        $code = $this->getFileContent('fc-mutates-return-typehint-fqcn-allows-null.php');
        $mutations = $this->mutate($code);

        $expectedMutatedCode = <<<"PHP"
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
PHP;

        $this->assertSame($expectedMutatedCode, $mutations[0]);
        $this->assertCount(1, $mutations);
    }

    public function test_it_mutates_when_scalar_return_typehint_allows_null(): void
    {
        $code = $this->getFileContent('fc-mutates-scalar-return-typehint-allows-null.php');
        $mutations = $this->mutate($code);

        $expectedMutatedCode = <<<"PHP"
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
PHP;

        $this->assertSame($expectedMutatedCode, $mutations[0]);
        $this->assertCount(1, $mutations);
    }

    public function test_it_mutates_when_function_contains_another_function_but_returns_function_call_and_null_allowed(): void
    {
        $code = $this->getFileContent('fc-contains-another-func-and-null-allowed.php');
        $mutations = $this->mutate($code);

        $expectedMutatedCode = <<<"PHP"
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
PHP;

        $this->assertSame($expectedMutatedCode, $mutations[0]);
        $this->assertCount(1, $mutations);
    }

    private function getFileContent(string $file): string
    {
        return file_get_contents(sprintf(__DIR__ . '/../../Fixtures/Autoloaded/FunctionCall/%s', $file));
    }
}
