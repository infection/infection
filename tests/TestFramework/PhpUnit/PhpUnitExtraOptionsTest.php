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

namespace Infection\Tests\TestFramework\PhpUnit;

use Infection\TestFramework\PhpUnit\PhpUnitExtraOptions;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PhpUnitExtraOptionsTest extends TestCase
{
    /**
     * @dataProvider mutantProcessProvider
     */
    public function test_it_skips_filter_for_mutant_process(string $sourceExtraOptions, string $expectedExtraOptions): void
    {
        $phpUnitOptions = new PhpUnitExtraOptions($sourceExtraOptions);

        $this->assertSame($expectedExtraOptions, $phpUnitOptions->getForMutantProcess());
    }

    public function test_it_returns_empty_string_when_source_options_are_null(): void
    {
        $phpUnitOptions = new PhpUnitExtraOptions(null);

        $this->assertSame('', $phpUnitOptions->getForInitialProcess());
        $this->assertSame('', $phpUnitOptions->getForMutantProcess());
    }

    public function mutantProcessProvider()
    {
        return [
            ['--filter=someTest#2 --a --b=value', '--a --b=value'],
            ['--a --filter=someTest#2 --b=value', '--a --b=value'],
            ['--a --filter someTest#2 --b=value', '--a --b=value'],
            ['--a --filter="some Test#2" --b=value', '--a --b=value'],
            ['--a --filter=\'some Test#2\' --b=value', '--a --b=value'],
            ['--testsuite=someTest#2 --a --b=value', '--a --b=value'],
            ['--a --testsuite=someTest#2 --b=value', '--a --b=value'],
            ['--a --testsuite someTest#2 --b=value', '--a --b=value'],
            ['--a --testsuite="some Test#2" --b=value', '--a --b=value'],
            ['--a --testsuite=\'some Test#2\' --b=value', '--a --b=value'],
        ];
    }
}
