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

namespace Infection\Tests\Configuration;

use Infection\Configuration\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Configuration::class)]
final class ConfigurationTest extends TestCase
{
    /**
     * @param string[] $expected
     */
    #[DataProvider('staticAnalysisToolOptionsProvider')]
    public function test_it_can_provide_the_static_analysis_tool_options(
        Configuration $configuration,
        array $expected,
    ): void {
        $actual = $configuration->getStaticAnalysisToolOptions();

        $this->assertSame($expected, $actual);
    }

    public static function staticAnalysisToolOptionsProvider(): iterable
    {
        yield 'no option' => [
            ConfigurationBuilder::withMinimalTestData()
                ->withStaticAnalysisToolOptions(null)
                ->build(),
            [],
        ];

        yield 'empty string option' => [
            ConfigurationBuilder::withMinimalTestData()
                ->withStaticAnalysisToolOptions('')
                ->build(),
            [],
        ];

        yield 'fake blank string option' => [
            ConfigurationBuilder::withMinimalTestData()
                ->withStaticAnalysisToolOptions('{u+020}')
                ->build(),
            ['--{u+020}'],
        ];

        yield 'single option' => [
            ConfigurationBuilder::withMinimalTestData()
                ->withStaticAnalysisToolOptions('--memory-limit=-1')
                ->build(),
            ['--memory-limit=-1'],
        ];

        // TODO: this looks weird...
        yield 'not an option' => [
            ConfigurationBuilder::withMinimalTestData()
                ->withStaticAnalysisToolOptions('src')
                ->build(),
            ['--src'],
        ];

        yield 'multiple options' => [
            ConfigurationBuilder::withMinimalTestData()
                ->withStaticAnalysisToolOptions('--memory-limit=2G --level=max')
                ->build(),
            ['--memory-limit=2G', '--level=max'],
        ];

        yield 'multiple options with one non-option' => [
            ConfigurationBuilder::withMinimalTestData()
                ->withStaticAnalysisToolOptions('src --memory-limit=2G --level=max tests')
                ->build(),
            [
                '--src',
                '--memory-limit=2G',
                '--level=max tests',    // TODO: this looks weird...
            ],
        ];
    }
}
