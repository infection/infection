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

namespace Infection\Tests\TestFramework\Coverage\JUnit;

use Infection\TestFramework\Coverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\Coverage\JUnit\JUnitTestFileDataProvider;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function Safe\file_put_contents;
use function Safe\tempnam;
use function Safe\unlink;

/**
 * @group integration
 */
final class JUnitTestFileDataProviderTest extends TestCase
{
    private const JUNIT = __DIR__ . '/../../../Fixtures/Files/phpunit/junit.xml';
    private const JUNIT_DIFF_FORMAT = __DIR__ . '/../../../Fixtures/Files/phpunit/junit2.xml';
    private const JUNIT_FEATURE_FORMAT = __DIR__ . '/../../../Fixtures/Files/phpunit/junit_feature.xml';

    /**
     * @var JUnitReportLocator|MockObject
     */
    private $jUnitLocatorMock;

    /**
     * @var JUnitTestFileDataProvider
     */
    private $provider;

    /**
     * @var string
     */
    private $tempfile;

    protected function setUp(): void
    {
        $this->jUnitLocatorMock = $this->createMock(JUnitReportLocator::class);

        $this->provider = new JUnitTestFileDataProvider($this->jUnitLocatorMock);

        $this->tempfile = tempnam('', '');
    }

    protected function tearDown(): void
    {
        unlink($this->tempfile);
    }

    public function test_it_returns_time_and_path(): void
    {
        $this->jUnitLocatorMock
            ->method('locate')
            ->willReturn(self::JUNIT)
        ;

        $testFileInfo = $this->provider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');

        $this->assertSame('/project/tests/Config/InfectionConfigTest.php', $testFileInfo->path);
        $this->assertSame(0.021983, $testFileInfo->time);
    }

    public function test_it_returns_the_same_result_on_consecutive_calls(): void
    {
        $this->jUnitLocatorMock
            ->method('locate')
            ->willReturn(self::JUNIT)
        ;

        $testFileInfo0 = $this->provider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');
        $testFileInfo1 = $this->provider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');

        $this->assertSame($testFileInfo0->path, $testFileInfo1->path);
        $this->assertSame($testFileInfo0->time, $testFileInfo1->time);
    }

    public function test_it_throws_an_exception_if_the_junit_file_is_invalid_xml(): void
    {
        $this->jUnitLocatorMock
            ->method('locate')
            ->willReturn($this->tempfile)
        ;

        $this->expectException(InvalidArgumentException::class);

        $this->provider->getTestFileInfo('Foo\BarTest');
    }

    public function test_it_works_with_different_junit_format(): void
    {
        $this->jUnitLocatorMock
            ->method('locate')
            ->willReturn(self::JUNIT_DIFF_FORMAT)
        ;

        $testFileInfo = $this->provider->getTestFileInfo('App\Tests\unit\SourceClassTest');

        $this->assertSame('/codeception/tests/unit/SourceClassTest.php', $testFileInfo->path);
    }

    public function test_it_works_with_feature_junit_format(): void
    {
        $this->jUnitLocatorMock
            ->method('locate')
            ->willReturn(self::JUNIT_FEATURE_FORMAT)
        ;

        $testFileInfo = $this->provider->getTestFileInfo('FeatureA:Scenario A1');

        $this->assertSame('/codeception/tests/bdd/FeatureA.feature', $testFileInfo->path);
    }

    /**
     * @dataProvider xmlProvider
     */
    public function test_it_does_not_trigger_count_assertion(string $xml): void
    {
        file_put_contents($this->tempfile, $xml);

        $this->jUnitLocatorMock
            ->method('locate')
            ->willReturn($this->tempfile)
        ;

        $this->provider->getTestFileInfo('ExampleTest');

        $this->addToAssertionCount(1);
    }

    public static function xmlProvider(): iterable
    {
        yield [<<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
    <testsuite name="ExampleTest"/>
    <testsuite name="ExampleTest"/>
</testsuites>
XML];

        yield [<<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
    <testcase class="ExampleTest"/>
    <testcase class="ExampleTest"/>
</testsuites>
XML];

        yield [<<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
    <testcase file="foo/ExampleTest.feature"/>
    <testcase file="foo/ExampleTest.feature"/>
</testsuites>
XML];
    }
}
