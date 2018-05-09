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
final class FloatNegationTest extends AbstractMutatorTestCase
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
            'It mutates negative float return to positive' => [
                <<<'PHP'
<?php

return -2.0;
PHP
                ,
                <<<'PHP'
<?php

return 2.0;
PHP
                ,
            ],
            'It mutates positive float return to negative' => [
                <<<'PHP'
<?php

return 2.0;
PHP
                ,
                <<<'PHP'
<?php

return -2.0;
PHP
                ,
            ],
            'It does not mutate float zero' => [
                <<<'PHP'
<?php

return 0.0;
PHP
                ,
            ],
            'It does not mutate integers' => [
                <<<'PHP'
<?php

return 1;
PHP
                ,
            ],
        ];
    }
}
