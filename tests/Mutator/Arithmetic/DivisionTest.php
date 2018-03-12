<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\Division;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class DivisionTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null)
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): array
    {
        return [
            'It changes regular divison' => [
                <<<'PHP'
<?php

$a = 10 / 2;
PHP
                ,
                <<<'PHP'
<?php

$a = 10 * 2;
PHP
                ,
            ],
            'It does not change division equals' => [
                <<<'PHP'
<?php

$a = 10;
$a /= 5;
PHP
                ,
            ],
        ];
    }

    public function test_replaces_division_with_multiplication()
    {
        $code = '<?php 1 / 2;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'PHP'
<?php

1 * 2;
PHP;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}
