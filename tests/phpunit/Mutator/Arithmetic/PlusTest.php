<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use PhpParser\Node\Expr\BinaryOp\Plus;
use Infection\Tests\Mutator\AbstractMutatorTestCase;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\LNumber;

final class PlusTest extends AbstractMutatorTestCase
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

    public function test_it_should_mutate_plus_expression(): void
    {
        $plusExpression = new Plus(new LNumber(1), new LNumber(2));

        $this->assertTrue($this->mutator->shouldMutate($plusExpression));
    }

    public function test_it_should_not_mutate_plus_with_arrays(): void
    {
        $plusExpression = new Plus(
            new Array_([new LNumber(1)]),
            new Array_([new LNumber(1)])
        );

        $this->assertFalse($this->mutator->shouldMutate($plusExpression));
    }
}
