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
final class CastIntTest extends AbstractMutatorTestCase
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
        yield 'It removes casting to int' => [
            <<<'PHP'
<?php

(int) 1.0;
PHP
            ,
            <<<'PHP'
<?php

1.0;
PHP
            ,
        ];

        yield 'It removes casting to integer' => [
            <<<'PHP'
<?php

(integer) 1.0;
PHP
            ,
            <<<'PHP'
<?php

1.0;
PHP
            ,
        ];
    }
}
