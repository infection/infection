<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Operator;

use Infection\Mutator\Mutator;
use Infection\Mutator\Operator\Throw_;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class ThrowTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new Throw_();
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
            'It removes the throw statement' => [
                <<<'PHP'
<?php

throw new \Exception();
PHP
                ,
                <<<'PHP'
<?php

new \Exception();
PHP
                ,
            ],
        ];
    }
}
