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

namespace Infection\Tests\TestFramework\PhpUnit\Coverage;

use Infection\TestFramework\Coverage\CoverageDoesNotExistException;
use Infection\TestFramework\Coverage\TestFileNameNotFoundException;
use Infection\TestFramework\PhpUnit\Coverage\PhpUnitTestFileDataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PhpUnitTestFileDataProviderTest extends TestCase
{
    /**
     * @var PhpUnitTestFileDataProvider
     */
    private $infoProvider;

    protected function setUp(): void
    {
        $this->infoProvider = new PhpUnitTestFileDataProvider(
            __DIR__ . '/../../../Fixtures/Files/phpunit/junit.xml'
        );
    }

    public function test_it_throws_an_exception_if_class_is_not_found(): void
    {
        $this->expectException(TestFileNameNotFoundException::class);

        $this->infoProvider->getTestFileInfo('abc');
    }

    public function test_it_returns_time_and_path(): void
    {
        $info = $this->infoProvider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');

        $this->assertSame('/project/tests/Config/InfectionConfigTest.php', $info->path);
        $this->assertSame(0.021983, $info->time);
    }

    public function test_consecutive_calls_with_the_same_class_return_the_same_result(): void
    {
        $info1 = $this->infoProvider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');
        $info2 = $this->infoProvider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');

        $this->assertSame($info1->path, $info2->path);
        $this->assertSame($info1->time, $info2->time);
    }

    public function test_it_throws_a_coverage_does_not_exists_exception_when_junit_file_does_not_exist(): void
    {
        $provider = new PhpUnitTestFileDataProvider('foo/bar/fake-file');

        $this->expectException(CoverageDoesNotExistException::class);

        $provider->getTestFileInfo('Foo\BarTest');
    }
}
