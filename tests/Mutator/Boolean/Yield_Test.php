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
final class Yield_Test extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null): void
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): \Generator
    {
        yield 'It mutates a yield with a double arrow to a yield with a greater than comparison' => [
            <<<'PHP'
<?php

$a = function () {
    (yield $a => $b);
};
PHP
            ,
            <<<'PHP'
<?php

$a = function () {
    (yield $a > $b);
};
PHP
            ,
        ];

        yield 'It does not mutate yields without a double arrow operator' => [
            <<<'PHP'
<?php

$a = function () {
    (yield $b);
};
PHP
            ,
        ];
    }
}
