<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ConditionalNegotiation;

use Infection\Mutator\ConditionalNegotiation\GreaterThanNegotiation;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;

class GreaterThanNegotiationTest extends AbstractMutator
{
    public function test_it_mutates_equal_to_not_equal()
    {
        $code = '<?php 1 > 1;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

1 <= 1;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    protected function getMutator(): Mutator
    {
        return new GreaterThanNegotiation();
    }
}
