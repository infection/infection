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

use function file_get_contents;
use Infection\TestFramework\Coverage\JUnit\JUnitTestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\TestFileNameNotFoundException;
use Infection\TestFramework\Coverage\JUnit\TestFileTimeData;
use Infection\TestFramework\Coverage\Locator\FixedLocator;
use Infection\Tests\FileSystem\FileSystemTestCase;
use Infection\Tests\TestingUtility\PHPUnit\DataProviderFactory;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use InvalidArgumentException;
use function is_string;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\file_put_contents;
use Symfony\Component\Filesystem\Path;
use Throwable;

#[Group('integration')]
#[CoversClass(JUnitTestFileDataProvider::class)]
final class JUnitTestFileDataProviderTest extends FileSystemTestCase
{
    use ExpectsThrowables;

    private const FIXTURES_DIR = __DIR__ . '/Fixtures';

    /**
     * @param non-empty-array<class-string, TestFileTimeData|class-string<Throwable>> $expected
     */
    #[DataProvider('infoProvider')]
    public function test_it_can_get_the_test_info_for_a_given_test_id(
        string $xml,
        string $testId,
        TestFileTimeData|string $expected,
    ): void {
        $provider = $this->createProvider(
            $this->createJUnit($xml),
        );

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
        yield from DataProviderFactory::prefix(
            '[PHPUnit 09] ',
            self::phpUnit09InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 10] ',
            self::phpUnit10InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 11] ',
            self::phpUnit11InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 12] ',
            self::phpUnit12InfoProvider(),
        );

        // https://codeception.com/docs/UnitTests
        yield from DataProviderFactory::prefix(
            '[Codeception (unit)] ',
            self::codeceptionUnitProvider(),
        );

        // https://codeception.com/docs/BDD
        yield from DataProviderFactory::prefix(
            '[Codeception (BDD style)] ',
            self::codeceptionBddProvider(),
        );

        // https://codeception.com/docs/AdvancedUsage#Cest-Classes
        yield from DataProviderFactory::prefix(
            '[Codeception (Cest style)] ',
            self::codeceptionCestProvider(),
        );
    }

    /**
     * @param non-empty-array<class-string, TestFileTimeData|class-string<Throwable>> $expected
     */
    #[DataProvider('infoProvider')]
    public function test_it_is_idempotent(
        string $xml,
        string $testId,
        TestFileTimeData|string $expected,
    ): void {
        $provider = $this->createProvider(
            $this->createJUnit($xml),
        );

        if (is_string($expected)) {
            $resultOfTheFirstCall = $this->expectToThrow(
                static fn () => $provider->getTestFileInfo($testId),
            );
            $resultOfTheSecondCall = $this->expectToThrow(
                static fn () => $provider->getTestFileInfo($testId),
            );

            $this->assertEquals($resultOfTheFirstCall, $resultOfTheSecondCall);
        } else {
            $resultOfTheFirstCall = $provider->getTestFileInfo($testId);
            $resultOfTheSecondCall = $provider->getTestFileInfo($testId);

            $this->assertEquals($resultOfTheFirstCall, $resultOfTheSecondCall);
        }
    }

    public function test_it_throws_an_exception_if_the_junit_file_is_invalid_xml(): void
    {
        $provider = $this->createProvider(
            $this->createJUnit(''),
        );

        // TODO: this is not ideal...
        $this->expectException(InvalidArgumentException::class);

        $provider->getTestFileInfo('Foo\BarTest');
    }

    private function createProvider(string $file): JUnitTestFileDataProvider
    {
        return new JUnitTestFileDataProvider(
            new FixedLocator($file),
        );
    }

    private static function phpUnit09InfoProvider(): iterable
    {
        $junitXml = file_get_contents(self::FIXTURES_DIR . '/phpunit-09-junit.xml');

        yield 'TestCase classname' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest',
            new TestFileTimeData(
                '/path/to/infection/tests/e2e/PHPUnit_09-3/tests/Covered/CalculatorTest.php',
                0.006446,
            ),
        ];

        yield 'test ID of a simple test' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_multiply',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a simple test' => [
            $junitXml,
            'test_multiply',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a data provider with a numerical key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract#0',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a data provider with a string key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract#with a key',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a data provider with a string key with special characters' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract#with a key with (\'&quot;#::&amp;) special characters',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a test with a data provider' => [
            $junitXml,
            'test_subtract',
            TestFileNameNotFoundException::class,
        ];

        yield 'test case of a data provider' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract',
            new TestFileTimeData(
                '',
                0.000386,
            ),
        ];

        yield 'test ID of a test with an external data provider with a numerical key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add#0',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with an external data provider with a string key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add#with a key',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with an external data provider with a string key with special characters' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add#with a key with (\'&quot;#::&amp;) special characters',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a test with an external data provider' => [
            $junitXml,
            'test_add',
            TestFileNameNotFoundException::class,
        ];

        yield 'test case of an external data provider' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add',
            new TestFileTimeData(
                '',
                0.004078,
            ),
        ];
    }

    private static function phpUnit10InfoProvider(): iterable
    {
        $junitXml = file_get_contents(self::FIXTURES_DIR . '/phpunit-10-junit.xml');

        yield 'TestCase classname' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest',
            new TestFileTimeData(
                '/path/to/infection/tests/e2e/PHPUnit_10-1/tests/Covered/CalculatorTest.php',
                0.026407,
            ),
        ];

        yield 'test ID of a simple test' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_multiply',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a simple test' => [
            $junitXml,
            'test_multiply',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a data provider with a numerical key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_subtract#0',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a data provider with a string key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_subtract#with a key',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a data provider with a string key with special characters' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_subtract#with a key with (\'&quot;#::&amp;) special characters',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a test with a data provider' => [
            $junitXml,
            'test_subtract',
            TestFileNameNotFoundException::class,
        ];

        yield 'test case of a data provider' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_subtract',
            new TestFileTimeData(
                '',
                0.003736,
            ),
        ];

        yield 'test ID of a test with an external data provider with a numerical key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_add#0',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with an external data provider with a string key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_add#with a key',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with an external data provider with a string key with special characters' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_add#with a key with (\'&quot;#::&amp;) special characters',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a test with an external data provider' => [
            $junitXml,
            'test_add',
            TestFileNameNotFoundException::class,
        ];

        yield 'test case of an external data provider' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_add',
            new TestFileTimeData(
                '',
                0.016477,
            ),
        ];
    }

    private static function phpUnit11InfoProvider(): iterable
    {
        $junitXml = file_get_contents(self::FIXTURES_DIR . '/phpunit-11-junit.xml');

        yield 'TestCase classname' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest',
            new TestFileTimeData(
                '/path/to/infection/tests/e2e/PHPUnit_11/tests/Covered/CalculatorTest.php',
                0.02126,
            ),
        ];

        yield 'test ID of a simple test' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_multiply',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a simple test' => [
            $junitXml,
            'test_multiply',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a data provider with a numerical key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_subtract#0',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a data provider with a string key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_subtract#with a key',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a data provider with a string key with special characters' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_subtract#with a key with (\'&quot;#::&amp;) special characters',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a test with a data provider' => [
            $junitXml,
            'test_subtract',
            TestFileNameNotFoundException::class,
        ];

        yield 'test case of a data provider' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_subtract',
            new TestFileTimeData(
                '',
                0.002973,
            ),
        ];

        yield 'test ID of a test with an external data provider with a numerical key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_add#0',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with an external data provider with a string key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_add#with a key',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with an external data provider with a string key with special characters' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_add#with a key with (\'&quot;#::&amp;) special characters',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a test with an external data provider' => [
            $junitXml,
            'test_add',
            TestFileNameNotFoundException::class,
        ];

        yield 'test case of an external data provider' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_add',
            new TestFileTimeData(
                '',
                0.01186,
            ),
        ];
    }

    private static function phpUnit12InfoProvider(): iterable
    {
        $junitXml = file_get_contents(self::FIXTURES_DIR . '/phpunit-12-junit.xml');

        yield 'TestCase classname' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest',
            new TestFileTimeData(
                '/path/to/infection/tests/e2e/PHPUnit_12-0/tests/Covered/CalculatorTest.php',
                0.022453,
            ),
        ];

        yield 'test ID of a simple test' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_multiply',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a simple test' => [
            $junitXml,
            'test_multiply',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a data provider with a numerical key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_subtract#0',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a data provider with a string key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_subtract#with a key',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a data provider with a string key with special characters' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_subtract#with a key with (\'&quot;#::&amp;) special characters',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a test with a data provider' => [
            $junitXml,
            'test_subtract',
            TestFileNameNotFoundException::class,
        ];

        yield 'test case of a data provider' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_subtract',
            new TestFileTimeData(
                '',
                0.003307,
            ),
        ];

        yield 'test ID of a test with an external data provider with a numerical key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_add#0',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with an external data provider with a string key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_add#with a key',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with an external data provider with a string key with special characters' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_add#with a key with (\'&quot;#::&amp;) special characters',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a test with an external data provider' => [
            $junitXml,
            'test_add',
            TestFileNameNotFoundException::class,
        ];

        yield 'test case of an external data provider' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_add',
            new TestFileTimeData(
                '',
                0.011771,
            ),
        ];
    }

    private static function codeceptionUnitProvider(): iterable
    {
        $junitXml = file_get_contents(self::FIXTURES_DIR . '/codeception-junit.xml');

        yield 'TestSuite' => [
            $junitXml,
            'Codeception_With_Suite_Overridings.unit',
            new TestFileTimeData(
                '',
                0.039222,
            ),
        ];

        yield 'TestCase classname' => [
            $junitXml,
            'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest',
            new TestFileTimeData(
                '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/unit/Covered/CalculatorTest.php',
                0.004677,
            ),
        ];

        yield 'test ID of a simple test' => [
            $junitXml,
            // Note that unlike PHPUnit (despite using it), it uses `:` instead of `::`
            'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testMultiplication',
            new TestFileTimeData(
                '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/unit/Covered/CalculatorTest.php',
                0.004677,
            ),
        ];

        yield 'test method of a simple test' => [
            $junitXml,
            'testMultiplication',
            TestFileNameNotFoundException::class,
        ];

        // Codeception does not understand PHPUnit data providers.
        // Instead of having:
        //     Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_subtract#0
        // Codeception will have:
        //     Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest:test_subtract
        // As if no data provider was configured... local like external.
        // This means in the JUnit report, we can see:
        //     <testcase name="testAddition" class="Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest" file="/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/functional/CalculatorCest.php" time="0.000252" assertions="1"/>
        // appearing multiple times due to the data providers, but with no way to distinguish them...
    }

    private static function codeceptionBddProvider(): iterable
    {
        $junitXml = file_get_contents(self::FIXTURES_DIR . '/codeception-junit.xml');

        yield 'TestSuite' => [
            $junitXml,
            'Codeception_With_Suite_Overridings.acceptance',
            new TestFileTimeData(
                '',
                0.013694,
            ),
        ];

        yield 'TestCase (feature)' => [
            $junitXml,
            'Calculator',
            // TODO: this is not ideal
            InvalidArgumentException::class,
        ];

        yield 'test ID of a simple test (ID found in a JUnit report)' => [
            $junitXml,
            'Calculator: Dividing two numbers',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a simple test (ID found in a CoverageXML report)' => [
            $junitXml,
            'calculator:Dividing two numbers',
            new TestFileTimeData(
                '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/acceptance/calculator.feature',
                0.003794,
            ),
        ];

        yield 'test method of a simple test' => [
            $junitXml,
            'Dividing two numbers',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a scenario outline (ID found in a JUnit report)' => [
            $junitXml,
            'Calculator: Checking if numbers are positive | 5, true',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a scenario outline (ID found in a CoverageXML report)' => [
            $junitXml,
            'calculator:Checking if numbers are positive | 5, true',
            new TestFileTimeData(
                '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/acceptance/calculator.feature',
                0.003794,
            ),
        ];

        yield 'test method of a test with a scenario outline' => [
            $junitXml,
            'Checking if numbers are positive',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a scenario outline with a placeholder in the scenario title (ID found in a JUnit report)' => [
            $junitXml,
            'Calculator: Computing absolute value with label &quot;&lt;label&gt;&quot; | 42, 5, 5',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a scenario outline with a placeholder in the scenario title (ID found in a CoverageXML report)' => [
            $junitXml,
            'calculator:Computing absolute value with label &quot;&lt;label&gt;&quot; | 42, 5, 5',
            new TestFileTimeData(
                '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/acceptance/calculator.feature',
                0.003794,
            ),
        ];

        yield 'test ID of a test with a scenario outline with a placeholder in the scenario title with special characters (ID found in a JUnit report)' => [
            $junitXml,
            'Calculator: Computing absolute value with label &quot;&lt;label&gt;&quot; | with special chars (\'&quot;#::&amp;), -15, 15',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a scenario outline with a placeholder in the scenario title with special characters (ID found in a CoverageXML report)' => [
            $junitXml,
            'calculator:Computing absolute value with label &quot;&lt;label&gt;&quot; | with special chars (\'&quot;#::&amp;), -15, 15',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a test with a scenario outline with a placeholder in the scenario title' => [
            $junitXml,
            'Computing absolute value with label &quot;&lt;label&gt;&quot;',
            TestFileNameNotFoundException::class,
        ];
    }

    private static function codeceptionCestProvider(): iterable
    {
        $junitXml = file_get_contents(self::FIXTURES_DIR . '/codeception-junit.xml');

        yield 'TestSuite' => [
            $junitXml,
            'Codeception_With_Suite_Overridings.functional',
            new TestFileTimeData(
                '',
                0.008832,
            ),
        ];

        yield 'TestCase classname' => [
            $junitXml,
            'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest',
            new TestFileTimeData(
                '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/functional/CalculatorCest.php',
                0.001363,
            ),
        ];

        yield 'test ID of a simple test' => [
            $junitXml,
            // Note that unlike PHPUnit (despite using it), it uses `:` instead of `::`
            'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testIsPositive',
            new TestFileTimeData(
                '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/functional/CalculatorCest.php',
                0.001363,
            ),
        ];

        yield 'test method of a simple test' => [
            $junitXml,
            'testIsPositive',
            TestFileNameNotFoundException::class,
        ];

        // Codeception does not understand PHPUnit data providers.
        // Instead of having:
        //     Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_subtract#0
        // Codeception will have:
        //     Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest:test_subtract
        // As if no data provider was configured... local like external.
        // This means in the JUnit report, we can see:
        //     <testcase name="testAddition" class="Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest" file="/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/functional/CalculatorCest.php" time="0.000252" assertions="1"/>
        // appearing multiple times due to the data providers, but with no way to distinguish them...
    }

    private function createJUnit(string $contents): string
    {
        $pathname = Path::canonicalize($this->tmp . '/junit.xml');
        file_put_contents($pathname, $contents);

        return $pathname;
    }
}
