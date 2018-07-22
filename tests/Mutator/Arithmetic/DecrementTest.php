<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class DecrementTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null): void
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): array
    {
        return [
            'It replaces post decrement' => [
                <<<'PHP'
<?php

$a = 1; 
$a--;
PHP
                ,
                <<<'PHP'
<?php

$a = 1;
$a++;
PHP
                ,
            ],
            'It replaces pre decrement' => [
                <<<'PHP'
<?php

$a = 1;
--$a;
PHP
                ,
                <<<'PHP'
<?php

$a = 1;
++$a;
PHP
                ,
            ],
            'It does not change when its not a real decrement' => [
                <<<'PHP'
<?php

$b - -$a;
PHP
                ,
            ],
        ];
    }
}
