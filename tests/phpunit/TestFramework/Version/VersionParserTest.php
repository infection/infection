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

namespace Infection\Tests\TestFramework;

use Infection\TestFramework\InvalidVersion;
use Infection\TestFramework\Version\VersionParser;
use PHPUnit\Framework\TestCase;

final class VersionParserTest extends TestCase
{
    /**
     * @var VersionParser
     */
    private $versionParser;

    protected function setUp(): void
    {
        $this->versionParser = new VersionParser();
    }

    /**
     * @dataProvider versionProvider
     */
    public function test_it_parses_version_from_string(string $content, string $expectedVersion): void
    {
        $result = $this->versionParser->parse($content);

        $this->assertSame($expectedVersion, $result);
    }

    public function test_it_throws_exception_when_content_has_no_version_substring(): void
    {
        $this->expectException(InvalidVersion::class);
        $this->expectExceptionMessage('Expected "abc" to be contain a valid SemVer (sub)string value.');

        $this->versionParser->parse('abc');
    }

    public function versionProvider(): iterable
    {
        yield 'nominal stable' => ['7.0.2', '7.0.2'];

        yield 'nominal development' => ['0.2.8', '0.2.8'];

        yield 'stable variant' => ['v7.0.2', '7.0.2'];

        yield 'development variant' => ['v0.2.8', '0.2.8'];

        yield 'patch' => ['7.0.2-patch', '7.0.2-patch'];

        yield 'versioned patch' => ['7.0.2-patch.0', '7.0.2-patch.0'];

        yield 'RC' => ['7.0.2-rc', '7.0.2-rc'];

        yield 'uppercase RC' => ['7.0.2-RC', '7.0.2-RC'];

        yield 'versioned RC' => ['7.0.2-rc.0', '7.0.2-rc.0'];

        yield 'with spaces' => [' 7.0.2 ', '7.0.2'];

        yield 'nonsense suffix 0' => ['7.0.2foo', '7.0.2'];

        yield 'nonsense suffix 1' => ['7.0.2-foo', '7.0.2-foo'];

        yield 'Hoa' => ['3.17.05.02', '3.17.05'];

        yield 'Codeception' => ['Codeception 3.1.0', '3.1.0'];

        yield 'phpspec stable' => ['phpspec version 1.2.3', '1.2.3'];

        yield 'phpspec RC' => ['phpspec version 5.0.0-rc1', '5.0.0-rc1'];

        yield 'PHPUnit' => ['PHPUnit 7.5.11 by Sebastian Bergmann and contributors.', '7.5.11'];
    }
}
