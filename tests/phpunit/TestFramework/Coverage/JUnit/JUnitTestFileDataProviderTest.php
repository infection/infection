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
use Infection\Tests\TestingUtility\FS;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use InvalidArgumentException;
use function is_string;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use function Safe\unlink;
use Symfony\Component\Filesystem\Path;
use Throwable;

#[Group('integration')]
#[CoversClass(JUnitTestFileDataProvider::class)]
final class JUnitTestFileDataProviderTest extends TestCase
{
    use ExpectsThrowables;

    private const JUNIT = __DIR__ . '/../../../Fixtures/Files/phpunit/junit.xml';

    private const JUNIT_DIFF_FORMAT = __DIR__ . '/../../../Fixtures/Files/phpunit/junit2.xml';

    private const JUNIT_FEATURE_FORMAT = __DIR__ . '/../../../Fixtures/Files/phpunit/junit_feature.xml';

    private const JUNIT_CODECEPTION_CEST_FORMAT = __DIR__ . '/../../../Fixtures/Files/phpunit/junit_codeception_cest.xml';

    private string $generatedJunitPath;

    protected function setUp(): void
    {
        $this->generatedJunitPath = FS::tmpFile('JUnitTestFileDataProviderTest');
    }

    protected function tearDown(): void
    {
        unlink($this->generatedJunitPath);
    }

    /**
     * @param TestFileTimeData|class-string<Throwable> $expected
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
            $this->expectException($expected);

            $provider->getTestFileInfo($testId);
        } else {
            $actual = $provider->getTestFileInfo($testId);

            $this->assertEquals($expected, $actual);
        }
    }

    /**
     * @param TestFileTimeData|class-string<Throwable> $expected
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

    public static function infoProvider(): iterable
    {
        yield 'PHPUnit' => [
            file_get_contents(self::JUNIT),
            'Infection\Tests\Config\InfectionConfigTest',
            new TestFileTimeData(
                '/project/tests/Config/InfectionConfigTest.php',
                0.021983,
            ),
        ];

        yield 'Codeception unit tests' => [
            file_get_contents(self::JUNIT_DIFF_FORMAT),
            'App\Tests\unit\SourceClassTest',
            new TestFileTimeData(
                '/codeception/tests/unit/SourceClassTest.php',
                0.006096,
            ),
        ];

        yield 'Codeception BDD' => [
            file_get_contents(self::JUNIT_FEATURE_FORMAT),
            'FeatureA:Scenario A1',
            new TestFileTimeData(
                '/codeception/tests/bdd/FeatureA.feature',
                0.039365,
            ),
        ];

        yield 'Codeception Cest' => [
            file_get_contents(self::JUNIT_CODECEPTION_CEST_FORMAT),
            'app\controllers\ExampleCest:FeatureA',
            new TestFileTimeData(
                '/app/controllers/ExampleCest.php',
                1.0E-6,
            ),
        ];

        yield 'invalid XML' => [
            '',
            'Acme\Service',
            InvalidArgumentException::class,
        ];
    }

    private function createProvider(string $file): JUnitTestFileDataProvider
    {
        return new JUnitTestFileDataProvider(
            new FixedLocator($file),
        );
    }

    private function createJUnit(string $contents): string
    {
        $pathname = Path::canonicalize($this->generatedJunitPath);
        file_put_contents($pathname, $contents);

        return $pathname;
    }
}
