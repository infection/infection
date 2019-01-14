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

namespace Infection\Tests\Mutator\Util;

use Infection\Mutator\Boolean\FalseValue;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\Util\MutatorParser;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MutatorParserTest extends TestCase
{
    public function test_it_returns_default_mutators_when_no_input_mutators(): void
    {
        $parser = new MutatorParser(null, [1, 2, 3]);

        $this->assertSame([1, 2, 3], $parser->getMutators());
    }

    public function test_it_throws_an_exception_when_mutators_is_only_whitespace(): void
    {
        $parser = new MutatorParser('    ', [1, 2, 3]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "--mutators" option requires a value.');

        $parser->getMutators();
    }

    public function test_it_generates_a_single_mutator_from_the_input_string(): void
    {
        $parser = new MutatorParser('TrueValue', []);

        $mutatorList = $parser->getMutators();

        $this->assertCount(1, $mutatorList);
        $this->assertInstanceOf(TrueValue::class, array_shift($mutatorList));
    }

    public function test_it_generates_multiple_mutators_from_the_input_string(): void
    {
        $parser = new MutatorParser('TrueValue,FalseValue', []);

        $mutatorList = $parser->getMutators();

        $this->assertCount(2, $mutatorList);
        $this->assertInstanceOf(TrueValue::class, array_shift($mutatorList));
        $this->assertInstanceOf(FalseValue::class, array_shift($mutatorList));
    }
}
