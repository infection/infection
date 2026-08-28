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

namespace Infection\Tests\TestFramework\Tracing\Tracer;

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\FileSystem\FileSystem;
use Infection\TestFramework\Coverage\CoveredTraceProvider;
use Infection\TestFramework\Coverage\JUnit\JUnitTestExecutionInfoAdder;
use Infection\TestFramework\Coverage\JUnit\JUnitTestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\MemoizedTestFileDataProvider;
use Infection\TestFramework\Coverage\Locator\FixedLocator;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageParser;
use Infection\TestFramework\Coverage\XmlReport\PhpUnitXmlCoverageTraceProvider;
use Infection\TestFramework\Coverage\XmlReport\XmlCoverageParser;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\TestFramework\Tracing\TraceProviderAdapterTracer;
use Infection\TestFramework\Tracing\Tracer;
use Infection\Tests\Fixtures\TestFramework\Coverage\JUnit\FakeTestFileDataProvider;
use Infection\Tests\TestFramework\Tracing\Trace\TraceAssertion;
use Infection\Tests\TestingUtility\PHPUnit\DataProviderFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
#[CoversNothing]
final class TracerIntegrationTest extends TestCase
{
    #[DataProvider('traceProvider')]
    public function test_it_can_create_a_trace(
        string $indexXmlPath,
        ?string $junitXmlPath,
        Trace $expected,
    ): void {
        $tracer = $this->createTracer(
            $indexXmlPath,
            $junitXmlPath,
        );

        $actual = $tracer->trace(
            $expected->getSourceFileInfo(),
        );

        TraceAssertion::assertEquals($expected, $actual);
    }

    public static function traceProvider(): iterable
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
            '[PHPUnit 12.0] ',
            PhpUnit120Provider::infoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 12.5] ',
            PhpUnit125Provider::infoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[Codeception] ',
            CodeceptionProvider::infoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PhpSpec] ',
            PhpSpecProvider::infoProvider(),
        );
    }

    private function createTracer(
        string $indexXmlPath,
        ?string $junitXmlPath,
    ): Tracer {
        $testFrameworkAdapterStub = $this->createStub(TestFrameworkAdapter::class);
        $testFrameworkAdapterStub
            ->method('hasJUnitReport')
            ->willReturn($junitXmlPath !== null);

        $junitFileDataProvider = $junitXmlPath === null
            ? new FakeTestFileDataProvider()
            : new MemoizedTestFileDataProvider(
                new JUnitTestFileDataProvider(
                    new FixedLocator($junitXmlPath),
                ),
            );

        $fileSystemStub = $this->createFileSystemStub();

        return new TraceProviderAdapterTracer(
            new CoveredTraceProvider(
                new PhpUnitXmlCoverageTraceProvider(
                    indexLocator: new FixedLocator($indexXmlPath),
                    indexParser: new IndexXmlCoverageParser(
                        isSourceFiltered: false,
                        fileSystem: $fileSystemStub,
                    ),
                    parser: new XmlCoverageParser(
                        $fileSystemStub,
                    ),
                ),
                new JUnitTestExecutionInfoAdder(
                    $testFrameworkAdapterStub,
                    $junitFileDataProvider,
                ),
            ),
        );
    }

    private function createFileSystemStub(): FileSystem
    {
        $fileSystem = new FileSystem();

        $fileSystemStub = $this->createStub(FileSystem::class);
        $fileSystemStub
            ->method('isReadableFile')
            ->willReturnCallback($fileSystem->isReadableFile(...));
        $fileSystemStub
            ->method('readFile')
            ->willReturnCallback($fileSystem->readFile(...));

        // We are only interested in mocking the realPath check!
        // In this test, we do not ~~need~~ want to check that the source file exists as this
        // makes the tests too inflexible.
        // In a real run, this is what provides the guarantee that the constructed path makes
        // sense; in this test it is done by checking that the path we get at the end for the
        // source file is the one we expect.
        $fileSystemStub
            ->method('realPath')
            ->willReturnCallback(static fn (string $path): string => $path);

        return $fileSystemStub;
    }
}
