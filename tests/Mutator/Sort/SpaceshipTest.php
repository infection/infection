<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Sort;

use Infection\Mutator\Sort\Spaceship;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class SpaceshipTest extends AbstractMutatorTestCase
{
    public function test_get_name()
    {
        $this->assertSame('Sort-Spaceship', $this->getMutator()::getName());
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
            'It swaps spaceship operators' => [
                <<<'PHP'
<?php

$a <=> $b;
PHP
                ,
                <<<'PHP'
<?php

$b <=> $a;
PHP
                ,
            ],
        ];
    }
}
