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

namespace Infection\Tests\TestFramework\Coverage\PhpUnit;

use Infection\TestFramework\Coverage\CoverageDoesNotExistException;
use Infection\TestFramework\Coverage\PhpUnit\JUnitTestFileDataProvider;
use Infection\TestFramework\Coverage\PhpUnit\TestFileNameNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @group integration Requires some I/O operations
 */
final class JUnitTestFileDataProviderTest extends TestCase
{
    private const JUNIT = __DIR__ . '/../../../Fixtures/Files/phpunit/junit.xml';
    private const JUNIT_DIFF_FORMAT = __DIR__ . '/../../../Fixtures/Files/phpunit/junit2.xml';

    /**
     * @var JUnitTestFileDataProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new JUnitTestFileDataProvider(self::JUNIT);
    }

    public function test_it_returns_time_and_path(): void
    {
        $testFileInfo = $this->provider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');

        $this->assertSame('/project/tests/Config/InfectionConfigTest.php', $testFileInfo->path);
        $this->assertSame(0.021983, $testFileInfo->time);
    }

    public function test_it_returns_the_same_result_on_consecutive_calls(): void
    {
        $testFileInfo0 = $this->provider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');
        $testFileInfo1 = $this->provider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');

        $this->assertSame($testFileInfo0->path, $testFileInfo1->path);
        $this->assertSame($testFileInfo0->time, $testFileInfo1->time);
    }

    public function test_it_throws_an_exception_if_class_is_not_found(): void
    {
        $this->expectException(TestFileNameNotFoundException::class);

        $this->provider->getTestFileInfo('abc');
    }

    public function test_it_throws_an_exception_if_the_junit_file_does_not_exist(): void
    {
        $provider = new JUnitTestFileDataProvider('foo/bar/fake-file');

        $this->expectException(CoverageDoesNotExistException::class);

        $provider->getTestFileInfo('Foo\BarTest');
    }

    public function test_it_works_with_different_junit_format(): void
    {
        $provider = new JUnitTestFileDataProvider(self::JUNIT_DIFF_FORMAT);

        $testFileInfo = $provider->getTestFileInfo('App\Tests\unit\SourceClassTest');

        $this->assertSame('/codeception/tests/unit/SourceClassTest.php', $testFileInfo->path);
    }
}
