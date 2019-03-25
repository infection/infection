<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
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
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
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

//declare(strict_types=1);

namespace Infection\Tests\Mutator\Extensions;

use Generator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class BCMathTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator(string $input, string $expected = null, array $settings = []): void
    {
        $this->doTest($input, $expected, $settings);
    }

    public function provideMutationCases(): Generator
    {
        yield 'It does not convert bcadd when disabled' => [
            "<?php bcadd('1', '3');",
            null,
            ['settings' => ['bcadd' => false]],
        ];

        yield from $this->provideMutationCasesForBinaryOperator('bcadd', '+', 'summation');

        yield from $this->provideMutationCasesForBinaryOperator('bccomp', '<=>', 'spaceship');

        yield from $this->provideMutationCasesForBinaryOperator('bcdiv', '/', 'division');

        yield from $this->provideMutationCasesForBinaryOperator('bcmod', '%', 'modulo');

        yield from $this->provideMutationCasesForBinaryOperator('bcmul', '*', 'multiplication');

        yield from $this->provideMutationCasesForBinaryOperator('bcsub', '-', 'subtraction');

        yield from $this->provideMutationCasesForPowerOperator();

        yield from $this->provideMutationCasesForSquareRoot();

        yield from $this->provideMutationCasesForPowerModulo();
    }

    private function provideMutationCasesForBinaryOperator(string $bcFunc, string $op, string $expression): Generator
    {
        yield "It converts $bcFunc to $expression expression" => [
            "<?php \\$bcFunc('3', \$b);",
            "<?php\n\n(string) ('3' $op \$b);",
        ];

        yield "It converts $bcFunc with scale to $expression expression" => [
            "<?php $bcFunc(\$a, \$b, 2);",
            "<?php\n\n(string) (\$a $op \$b);",
        ];

        yield "It does not convert $bcFunc when not enough arguments" => [
            "<?php $bcFunc(\$a);",
        ];
    }

    private function provideMutationCasesForPowerOperator(): Generator
    {
        yield 'It converts bcpow to power expression' => [
            '<?php \\bcpow(5, $b);',
            "<?php\n\n(string) 5 ** \$b;",
        ];

        yield 'It converts bcpow with scale to power expression' => [
            '<?php bcpow($a, $b, 2);',
            "<?php\n\n(string) \$a ** \$b;",
        ];

        yield 'It does not convert bcpow when not enough arguments' => [
            '<?php bcpow($a);',
        ];
    }

    private function provideMutationCasesForSquareRoot(): Generator
    {
        yield 'It converts bcsqrt to sqrt call' => [
            '<?php \\bcsqrt(1, $b);',
            "<?php\n\n(string) \sqrt(1, \$b);",
        ];

        yield 'It converts bcsqrt with scale to sqrt call' => [
            '<?php bcsqrt($a, $b, 2);',
            "<?php\n\n(string) \sqrt(\$a, \$b);",
        ];

        yield 'It does not convert bcsqrt when not enough arguments' => [
            '<?php bcsqrt($a);',
        ];
    }

    private function provideMutationCasesForPowerModulo(): Generator
    {
        yield 'It converts bcpowmod to power modulo expression' => [
            '<?php \\bcpowmod($a, $b, $mod);',
            "<?php\n\n(string) (\pow(\$a, \$b) % \$mod);",
        ];

        yield 'It converts bcpowmod with scale to power modulo expression' => [
            '<?php bcpowmod($a, $b, 2);',
            "<?php\n\n(string) (\pow(\$a, \$b) % 2);",
        ];

        yield 'It does not convert bcpowmod when not enough arguments' => [
            '<?php bcpowmod($a, $b);',
        ];
    }
}
