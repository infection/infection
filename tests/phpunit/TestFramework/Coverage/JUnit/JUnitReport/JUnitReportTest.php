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

namespace Infection\Tests\TestFramework\Coverage\JUnit\JUnitReport;

use Infection\TestFramework\Coverage\JUnit\JUnitReport;
use Infection\Tests\TestingUtility\FS;
use Infection\Tests\TestingUtility\PHPUnit\DataProviderFactory;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use InvalidArgumentException;
use function is_string;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\file_put_contents;
use function Safe\unlink;
use Symfony\Component\Filesystem\Path;
use Throwable;

/**
 * @phpstan-import-type TestInfo from JUnitReport
 */
#[Group('integration')]
#[CoversClass(JUnitReport::class)]
final class JUnitReportTest extends TestCase
{
    use ExpectsThrowables;

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
     * @param TestInfo|class-string<Throwable> $expected
     */
    #[DataProvider('infoProvider')]
    public function test_it_can_get_the_test_info_for_a_given_test_id(
        string $xml,
        string $testId,
        array|string $expected,
    ): void {
        $provider = new JUnitReport(
            $this->createJUnit($xml),
        );

        if (is_string($expected)) {
            $this->expectException($expected);

            $provider->getTestInfo($testId);
        } else {
            $actual = $provider->getTestInfo($testId);

            $this->assertEquals($expected, $actual);
        }
    }

    /**
     * @param TestInfo|class-string<Throwable> $expected
     */
    #[DataProvider('infoProvider')]
    public function test_it_is_idempotent(
        string $xml,
        string $testId,
        array|string $expected,
    ): void {
        $report = new JUnitReport(
            $this->createJUnit($xml),
        );

        if (is_string($expected)) {
            $resultOfTheFirstCall = $this->expectToThrow(
                static fn () => $report->getTestInfo($testId),
            );
            $resultOfTheSecondCall = $this->expectToThrow(
                static fn () => $report->getTestInfo($testId),
            );

            $this->assertEquals($resultOfTheFirstCall, $resultOfTheSecondCall);
        } else {
            $resultOfTheFirstCall = $report->getTestInfo($testId);
            $resultOfTheSecondCall = $report->getTestInfo($testId);

            $this->assertEquals($resultOfTheFirstCall, $resultOfTheSecondCall);
        }
    }

    public static function infoProvider(): iterable
    {
        yield from DataProviderFactory::prefix(
            '[PHPUnit 09] ',
            PhpUnit09Provider::infoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 10] ',
            PhpUnit10Provider::infoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 11] ',
            PhpUnit11Provider::infoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 12] ',
            PhpUnit12Provider::infoProvider(),
        );

        // https://codeception.com/docs/UnitTests
        yield from DataProviderFactory::prefix(
            '[Codeception (unit)] ',
            CodeceptionUnitProvider::infoProvider(),
        );

        // https://codeception.com/docs/BDD
        yield from DataProviderFactory::prefix(
            '[Codeception (BDD style)] ',
            CodeceptionBDDProvider::infoProvider(),
        );

        // https://codeception.com/docs/AdvancedUsage#Cest-Classes
        yield from DataProviderFactory::prefix(
            '[Codeception (Cest style)] ',
            CodeceptionCestProvider::infoProvider(),
        );

        yield 'invalid XML' => [
            '',
            'Acme\Service',
            InvalidArgumentException::class,
        ];
    }

    private function createJUnit(string $contents): string
    {
        $pathname = Path::canonicalize($this->generatedJunitPath);
        file_put_contents($pathname, $contents);

        return $pathname;
    }
}
