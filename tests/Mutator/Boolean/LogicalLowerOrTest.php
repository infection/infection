<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Boolean;

use Infection\Mutator\Boolean\LogicalLowerOr;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class LogicalLowerOrTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new LogicalLowerOr();
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
            'It mutates logical lower or' => [
                <<<'PHP'
<?php

true or false;
PHP
                ,
                <<<'PHP'
<?php

true and false;
PHP
                ,
            ],
            'It does not mutate logical or' => [
                <<<'PHP'
<?php

true || false;
PHP
                ,
            ],
        ];
    }
}
