<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Sort;

use Infection\Mutator\Mutator;
use Infection\Mutator\Sort\Spaceship;
use Infection\Tests\Mutator\AbstractMutator;

class SpaceshipTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new Spaceship();
    }

    public function test_swap_spaceship_operator_arguments()
    {
        $code = '<?php $a <=> $b;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

$b <=> $a;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}
