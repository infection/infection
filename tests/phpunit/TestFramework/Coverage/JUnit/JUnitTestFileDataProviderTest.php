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

use Infection\TestFramework\Coverage\JUnit\JUnitTestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\TestFileTimeData;
use Infection\TestFramework\Coverage\Locator\FixedLocator;
use Infection\TestFramework\Coverage\Locator\ReportLocator;
use Infection\Tests\FileSystem\FileSystemTestCase;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;
use function file_get_contents;
use function is_string;
use function Safe\file_put_contents;
use function Safe\tempnam;
use function Safe\unlink;

#[Group('integration')]
#[CoversClass(JUnitTestFileDataProvider::class)]
final class JUnitTestFileDataProviderTest extends FileSystemTestCase
{
    use ExpectsThrowables;

    private const FIXTURES_DIR = __DIR__ . '/Fixtures';

    private const JUNIT = __DIR__ . '/../../../Fixtures/Files/phpunit/junit.xml';

    private const JUNIT_DIFF_FORMAT = __DIR__ . '/../../../Fixtures/Files/phpunit/junit2.xml';

    private const JUNIT_FEATURE_FORMAT = __DIR__ . '/../../../Fixtures/Files/phpunit/junit_feature.xml';

    private const JUNIT_CODECEPTION_CEST_FORMAT = __DIR__ . '/../../../Fixtures/Files/phpunit/junit_codeception_cest.xml';

    private function createProvider(string $file): JUnitTestFileDataProvider
    {
        return new JUnitTestFileDataProvider(
            new FixedLocator($file),
        );
    }

    /**
     * @param non-empty-array<class-string, TestFileTimeData|class-string<Throwable>> $expected
     */
    #[DataProvider('infoProvider')]
    public function test_it_can_get_the_test_info_for_a_given_test_id(
        string $xml,
        string $testId,
        TestFileTimeData|string $expected,
    ): void {
        $junit = $this->tmp.'/junit.xml';
        file_put_contents($junit, $xml);

        $provider = $this->createProvider($junit);

        if (is_string($expected)) {
            $actualException = $this->expectToThrow(
                static fn () => $provider->getTestFileInfo($testId),
            );

            $this->assertInstanceOf($expected, $actualException);
        } else {
            $actual = $provider->getTestFileInfo($testId);

            $this->assertEquals($expected, $actual);
        }
    }

    public static function infoProvider(): iterable
    {
        yield '[PHPUnit 10] TestCase classname' => [
            file_get_contents(self::FIXTURES_DIR.'/phpunit-10-junit.xml'),
            'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest',
            new TestFileTimeData(
                '/path/to/infection/tests/e2e/PHPUnit_10-1/tests/Covered/CalculatorTest.php',
                0.038394,
            ),
        ];
    }

    public function test_it_returns_the_same_result_on_consecutive_calls(): void
    {
        $provider = $this->createProvider(self::JUNIT);

        $testFileInfo0 = $provider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');
        $testFileInfo1 = $provider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');

        $this->assertSame($testFileInfo0->path, $testFileInfo1->path);
        $this->assertSame($testFileInfo0->time, $testFileInfo1->time);
    }

    public function test_it_throws_an_exception_if_the_junit_file_is_invalid_xml(): void
    {
        $junit = $this->tmp.'/junit.xml';
        file_put_contents($junit, '');

        $provider = $this->createProvider($junit);

        $this->expectException(InvalidArgumentException::class);

        $provider->getTestFileInfo('Foo\BarTest');
    }

    public function test_it_works_with_different_junit_format(): void
    {
        $provider = $this->createProvider(self::JUNIT_DIFF_FORMAT);

        $testFileInfo = $provider->getTestFileInfo('App\Tests\unit\SourceClassTest');

        $this->assertSame('/codeception/tests/unit/SourceClassTest.php', $testFileInfo->path);
    }

    public function test_it_works_with_feature_junit_format(): void
    {
        $provider = $this->createProvider(self::JUNIT_FEATURE_FORMAT);

        $testFileInfo = $provider->getTestFileInfo('FeatureA:Scenario A1');

        $this->assertSame('/codeception/tests/bdd/FeatureA.feature', $testFileInfo->path);
    }

    public function test_it_works_with_codeception_cest_format(): void
    {
        $provider = $this->createProvider(self::JUNIT_CODECEPTION_CEST_FORMAT);

        $testFileInfo = $provider->getTestFileInfo('app\controllers\ExampleCest:FeatureA');

        $this->assertSame('/app/controllers/ExampleCest.php', $testFileInfo->path);
    }

    #[DataProvider('xmlProvider')]
    public function test_it_does_not_trigger_count_assertion(string $xml): void
    {
        $junit = $this->tmp.'/junit.xml';
        file_put_contents($junit, $xml);

        $provider = $this->createProvider($junit);

        $provider->getTestFileInfo('ExampleTest');

        $this->expectNotToPerformAssertions();
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
