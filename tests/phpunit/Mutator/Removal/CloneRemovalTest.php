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

namespace Infection\Tests\Mutator\Removal;

use Generator;
use Infection\Tests\Mutator\BaseMutatorTestCase;

final class CloneRemovalTest extends BaseMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator(string $input, ?string $expected): void
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): Generator
    {
        yield 'It removes clone from expression clone-new' => [
          <<<'PHP'
<?php

$class = clone (new stdClass());
PHP
            ,
            <<<'PHP'
<?php

$class = new stdClass();
PHP
            ,
        ];

        yield 'It removes clone from clone variable' => [
            <<<'PHP'
<?php

$class = new stdClass();
$clonedClass = clone $class;
PHP
            ,
            <<<'PHP'
<?php

$class = new stdClass();
$clonedClass = $class;
PHP
            ,
        ];

        yield 'It removes cloe from direct call object function right after cloning' => [
            <<<'PHP'
<?php

$datetime = new DateTime();
$clonedClass = (clone $datetime)->format('Y');
PHP
            ,
            <<<'PHP'
<?php

$datetime = new DateTime();
$clonedClass = $datetime->format('Y');
PHP
            ,
        ];
    }
}
