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

namespace Infection\Tests\Mutator\ReturnValue;

use Generator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;
use function Safe\file_get_contents;
use function Safe\sprintf;

/**
 * @group integration
 */
final class ThisTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider mutationsProvider
     *
     * @param string|string[] $expected
     */
    public function test_it_can_mutate(string $input, $expected = []): void
    {
        $this->doTest($input, $expected);
    }

    public function mutationsProvider(): Generator
    {
        yield 'It does mutate with no typehint' => [
            $this->getFileContent('this_return-this.php'),
            <<<'PHP'
<?php

namespace This_ReturnThis;

class Test
{
    function test()
    {
        return null;
    }
}
PHP
        ];

        yield 'It does not mutate non \'this\' return statements' => [
            $this->getFileContent('this-return-types.php'),
        ];
    }

    private function getFileContent(string $file): string
    {
        return file_get_contents(sprintf(__DIR__ . '/../../Fixtures/Autoloaded/This_/%s', $file));
    }
}
