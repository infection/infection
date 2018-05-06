<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Cast;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class CastBoolTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null)
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): \Generator
    {
        yield 'It removes casting to bool' => [
            <<<'PHP'
<?php

(bool) 1;
(boolean) 1;
PHP
            ,
            <<<'PHP'
<?php

1;
1;
PHP
            ,
        ];
    }
}
