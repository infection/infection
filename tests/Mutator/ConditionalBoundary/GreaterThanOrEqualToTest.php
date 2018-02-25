<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ConditionalBoundary;

use Infection\Mutator\ConditionalBoundary\GreaterThanOrEqualTo;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class GreaterThanOrEqualToTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new GreaterThanOrEqualTo();
    }

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
            'It mutates greater than or equal to' => [
                <<<'PHP'
<?php

1 >= 2;
PHP
                ,
                <<<'PHP'
<?php

1 > 2;
PHP
                ,
            ],
            'It does not mutate an arrow' => [
                <<<'PHP'
<?php

[1 => 2];
PHP
                ,
            ],
            'It does not mutate a spaceship' => [
                <<<'PHP'
<?php

1 <=> 2;
PHP
                ,
            ],
        ];
    }
}
