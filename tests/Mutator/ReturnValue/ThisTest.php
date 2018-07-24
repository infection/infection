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
final class ThisTest extends AbstractMutatorTestCase
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
        yield 'It does mutate with no typehint' => [
            $this->getFileContent('this_return-this.php'),
            <<<'PHP'
<?php

namespace This_ReturnThis;

class Test
{
    function test()
    {
        return null;
    }
}
PHP
        ];

        yield 'It does not mutate non \'this\' return statements' => [
            $this->getFileContent('this-return-types.php'),
        ];
    }

    private function getFileContent(string $file): string
    {
        return file_get_contents(sprintf(__DIR__ . '/../../Fixtures/Autoloaded/This_/%s', $file));
    }
}
