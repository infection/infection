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

namespace Infection\Tests\Architecture\PHPStan\Rules;

use Override;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @extends RuleTestCase<InvalidArgumentExceptionRule>
 */
#[CoversClass(InvalidArgumentExceptionRule::class)]
final class InvalidArgumentExceptionRuleTest extends RuleTestCase
{
    /**
     * @param list<array{string, int}> $expected
     */
    #[DataProvider('fileProvider')]
    public function test_it_reports_direct_invalid_argument_exception_construction(
        string $file,
        array $expected,
    ): void {
        $fixturePath = __DIR__ . '/Fixtures/' . $file;

        $this->analyse(
            files: [$fixturePath],
            expectedErrors: $expected,
        );
    }

    public static function fileProvider(): iterable
    {
        yield 'production code' => [
            'Source/exceptions.php.fixture',
            [
                ['Use Webmozart\\Assert\\Assert instead of instantiating InvalidArgumentException.', 10],
                ['Use Webmozart\\Assert\\Assert instead of instantiating InvalidArgumentException.', 11],
                ['Use Webmozart\\Assert\\Assert instead of instantiating InvalidArgumentException.', 12],
            ],
        ];

        yield 'outside source, with a shared directory prefix' => [
            'SourceOutside/exceptions.php.fixture',
            [],
        ];
    }

    #[Override]
    protected function getRule(): InvalidArgumentExceptionRule
    {
        return new InvalidArgumentExceptionRule(__DIR__ . '/Fixtures/Source');
    }
}
