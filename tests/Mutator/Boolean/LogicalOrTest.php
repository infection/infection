<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Boolean;

use Infection\Mutator\Boolean\LogicalOr;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class LogicalOrTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new LogicalOr();
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
            'It mutates logical or' => [
                <<<'PHP'
<?php

true || false;
PHP
                ,
                <<<'PHP'
<?php

true && false;
PHP
                ,
            ],
            'It does not mutate logical lower or' => [
                <<<'PHP'
<?php

true or false;
PHP
                ,
            ],
        ];
    }
}
