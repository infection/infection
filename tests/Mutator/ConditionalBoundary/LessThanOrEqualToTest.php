<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\ConditionalBoundary;

use Infection\Mutator\ConditionalBoundary\LessThanOrEqualTo;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class LessThanOrEqualToTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new LessThanOrEqualTo();
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
            'It mutates less than or equal to' => [
                <<<'CODE'
<?php

1 <= 2;
CODE
                ,
                <<<'CODE'
<?php

1 < 2;
CODE
                ,
            ],
            'It does not mutate an arrow' => [
                <<<'CODE'
<?php

[1 => 2];
CODE
                ,
            ],
            'It does not mutate a spaceship' => [
                <<<'CODE'
<?php

1 <=> 2;
CODE
                ,
            ],
        ];
    }
}
