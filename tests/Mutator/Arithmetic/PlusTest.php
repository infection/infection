<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Tests\Mutator\AbstractMutatorTestCase;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\LNumber;

/**
 * @internal
 */
final class PlusTest extends AbstractMutatorTestCase
{
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
            'It mutates normal plus' => [
                <<<'PHP'
<?php

$a = 10 + 3;
PHP
                ,
                <<<'PHP'
<?php

$a = 10 - 3;
PHP
                ,
            ],
            'It does not mutate plus equals' => [
                <<<'PHP'
<?php

$a = 1;
$a += 2;
PHP
                ,
            ],
            'It does not mutate increment' => [
                <<<'PHP'
<?php

$a = 1;
$a++;
PHP
                ,
            ],
            'It does mutate a fake increment' => [
                <<<'PHP'
<?php

$a = 1;
$a + +1;
PHP
                ,
                <<<'PHP'
<?php

$a = 1;
$a - +1;
PHP
                ,
                ],
                'It does not mutate additon of arrays' => [
                    <<<'PHP'
<?php

$a = [0 => 1] + [1 => 3];
$b = 1 + [1 => 3];
$c = [1 => 1] + 3;
PHP
                    ,
            ],
        ];
    }

    public function test_it_should_mutate_plus_expression()
    {
        $plusExpression = new Node\Expr\BinaryOp\Plus(new LNumber(1), new LNumber(2));

        $this->assertTrue($this->mutator->shouldMutate($plusExpression));
    }

    public function test_it_should_not_mutate_plus_with_arrays()
    {
        $plusExpression = new Node\Expr\BinaryOp\Plus(
            new Array_([new LNumber(1)]),
            new Array_([new LNumber(1)])
        );

        $this->assertFalse($this->mutator->shouldMutate($plusExpression));
    }
}
