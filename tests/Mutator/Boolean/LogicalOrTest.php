<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Boolean;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class LogicalOrTest extends AbstractMutatorTestCase
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
