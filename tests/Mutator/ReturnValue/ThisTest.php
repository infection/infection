<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use Infection\Mutator\ReturnValue\This;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class ThisTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new This();
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
        yield 'It does not mutate with not nullable return typehint' => [
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
    }

    private function getFileContent(string $file): string
    {
        return file_get_contents(sprintf(__DIR__ . '/../../Fixtures/Autoloaded/This_/%s', $file));
    }
}
