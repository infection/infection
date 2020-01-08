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

namespace Infection\Tests;

use Infection\Mutation;
use Infection\Mutator\Arithmetic\Plus;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

final class MutationTest extends TestCase
{
    public function test_it_correctly_generates_hash(): void
    {
        $attributes = [
            'startLine' => 3,
            'endLine' => 5,
            'startTokenPos' => 21,
            'endTokenPos' => 31,
            'startFilePos' => 43,
            'endFilePos' => 53,
        ];

        $mutation = new Mutation(
            '/abc.php',
            [],
            Plus::class,
            $attributes,
            'Interface_',
            new Node\Scalar\LNumber(1),
            0,
            [1, 2, 3]
        );

        $this->assertSame('2930c05082a35248987760a81b9f9a08', $mutation->getHash());
    }

    public function test_it_correctly_sets_original_file_ast(): void
    {
        $attributes = [
            'startLine' => 3,
            'endLine' => 5,
            'startTokenPos' => 21,
            'endTokenPos' => 31,
            'startFilePos' => 43,
            'endFilePos' => 53,
        ];
        $fileAst = ['file' => 'ast'];

        $mutation = new Mutation(
            '/abc.php',
            $fileAst,
            Plus::class,
            $attributes,
            'Interface_',
            new Node\Scalar\LNumber(1),
            0,
            [1, 2, 3]
        );

        $this->assertSame($fileAst, $mutation->getOriginalFileAst());
    }
}
