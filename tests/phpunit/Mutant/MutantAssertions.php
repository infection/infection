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

namespace Infection\Tests\Mutant;

use Infection\Mutant\Mutant;
use Infection\Mutation\Mutation;
use Infection\Tests\Mutation\MutationAssertions;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-require-extends TestCase
 */
trait MutantAssertions
{
    use MutationAssertions;

    public function assertMutantEquals(
        Mutant $expected,
        Mutant $actual,
    ): void {
        $this->assertSame($expected->getFilePath(), $actual->getFilePath());
        $this->assertSame($expected->getMutation(), $actual->getMutation());
        $this->assertSame($expected->getMutatedCode()->get(), $actual->getMutatedCode()->get());
        $this->assertSame($expected->getDiff()->get(), $actual->getDiff()->get());
        $this->assertSame($expected->isCoveredByTest(), $actual->isCoveredByTest());
        $this->assertEquals($expected->getTests(), $actual->getTests());
        $this->assertSame($expected->getPrettyPrintedOriginalCode()->get(), $actual->getPrettyPrintedOriginalCode()->get());
    }

    public function assertMutantStateIs(
        Mutant $mutant,
        string $expectedFilePath,
        Mutation $expectedMutation,
        string $expectedMutatedCode,
        string $expectedDiff,
        string $expectedPrettyPrintedOriginalCode,
    ): void {
        $this->assertSame($expectedFilePath, $mutant->getFilePath());
        $this->assertMutationEquals($expectedMutation, $mutant->getMutation());
        $this->assertSame($expectedMutatedCode, $mutant->getMutatedCode()->get());
        $this->assertSame($expectedDiff, $mutant->getDiff()->get());
        $this->assertSame($expectedPrettyPrintedOriginalCode, $mutant->getPrettyPrintedOriginalCode()->get());
    }
}
