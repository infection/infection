<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ConditionalNegotiation;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class IdenticalTest extends AbstractMutatorTestCase
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
            'It mutates strict comparison' => [
                <<<'PHP'
<?php

1 === 1;
PHP
                ,
                <<<'PHP'
<?php

1 !== 1;
PHP
                ,
            ],
            'It does not mutate not strict comparison' => [
                <<<'PHP'
<?php

1 == 1;
PHP
                ,
            ],
            'It does not mutate not comparison' => [
                <<<'PHP'
<?php

1 !== 1;
PHP
                ,
            ],
        ];
    }
}
