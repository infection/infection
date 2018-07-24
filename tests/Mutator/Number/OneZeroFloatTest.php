<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Number;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class OneZeroFloatTest extends AbstractMutatorTestCase
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
            'It mutates float one to zero' => [
                <<<'PHP'
<?php

10 + 1.0;
PHP
                ,
                <<<'PHP'
<?php

10 + 0.0;
PHP
                ,
            ],
            'It mutates float zero to one' => [
                <<<'PHP'
<?php

10 + 0.0;
PHP
                ,
                <<<'PHP'
<?php

10 + 1.0;
PHP
                ,
            ],
            'It does not mutate int zero to one' => [
                <<<'PHP'
<?php

10 + 0;
PHP
                ,
            ],
            'It does not mutate int one to zer0' => [
                <<<'PHP'
<?php

10 + 1;
PHP
                ,
            ],
            'It does not mutate the string 0.0' => [
                <<<'PHP'
<?php

'a' . '0.0';
PHP
            ],
            'It does not mutate other floats' => [
                <<<'PHP'
<?php

10 + 2.0;
10 + 1.1;
10 + 0.5;
PHP
                ,
            ],
        ];
    }
}
