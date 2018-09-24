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
final class FunctionCallTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null): void
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): \Generator
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

    public function test_it_does_not_mutate_a_function_outside_a_class(): void
    {
        $code = <<<"PHP"
<?php

function test()
{
    return 1;
}
PHP;

        $mutations = $this->mutate($code);

        $this->assertCount(0, $mutations);
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
