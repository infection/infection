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

namespace Infection\Tests\Testing\TestFramework\Debug;

use function base64_encode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Testing\TestFramework\Debug\DebugCommandLine;
use Infection\Testing\TestFramework\Debug\DebugTestFrameworkAdapter;
use function json_encode;
use const JSON_THROW_ON_ERROR;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\PhpExecutableFinder;

#[CoversClass(DebugTestFrameworkAdapter::class)]
final class DebugTestFrameworkAdapterTest extends TestCase
{
    private DebugTestFrameworkAdapter $adapter;

    protected function setUp(): void
    {
        $phpExecutableFinder = $this->createStub(PhpExecutableFinder::class);
        $phpExecutableFinder
            ->method('find')
            ->willReturn('/php')
        ;

        $this->adapter = new DebugTestFrameworkAdapter(
            '/debug.php',
            '/tmp/infection',
            new DebugCommandLine($phpExecutableFinder),
        );
    }

    public function test_it_describes_successful_debug_runs(): void
    {
        $output = <<<EOF
            DEBUG_TEST_FRAMEWORK_PASSED
            Memory: 16.00 MB

            EOF;

        $this->assertTrue($this->adapter->testsPass($output));
        $this->assertSame(16., $this->adapter->getMemoryUsed($output));
    }

    /**
     * @param string[] $phpExtraArgs
     * @param string[] $expected
     */
    #[DataProvider('initialCommandLineProvider')]
    public function test_it_builds_the_initial_command_line(
        string $extraOptions,
        array $phpExtraArgs,
        bool $skipCoverage,
        array $expected,
    ): void {
        $actual = $this->adapter->getInitialTestRunCommandLine(
            $extraOptions,
            $phpExtraArgs,
            $skipCoverage,
        );

        $this->assertSame($expected, $actual);
    }

    public static function initialCommandLineProvider(): iterable
    {
        yield 'without optional arguments' => [
            'extraOptions' => '',
            'phpExtraArgs' => [],
            'skipCoverage' => false,
            'expected' => self::createExpectedCommand([
                '/php',
                '/debug.php',
                '--stage',
                'test-framework-initial',
                '--log',
                '/tmp/infection',
            ]),
        ];

        yield 'with optional arguments' => [
            'extraOptions' => '--filter=test',
            'phpExtraArgs' => ['-d', 'memory_limit=-1'],
            'skipCoverage' => true,
            'expected' => self::createExpectedCommand([
                '/php',
                '-d',
                'memory_limit=-1',
                '/debug.php',
                '--stage',
                'test-framework-initial',
                '--log',
                '/tmp/infection',
            ]),
        ];
    }

    /**
     * @param TestLocation[] $coverageTests
     * @param string[] $expected
     */
    #[DataProvider('mutantCommandLineProvider')]
    public function test_it_builds_the_mutant_command_line(
        array $coverageTests,
        string $mutatedFilePath,
        string $mutationHash,
        string $mutationOriginalFilePath,
        string $extraOptions,
        array $expected,
    ): void {
        $actual = $this->adapter->getMutantCommandLine(
            $coverageTests,
            $mutatedFilePath,
            $mutationHash,
            $mutationOriginalFilePath,
            $extraOptions,
        );

        $this->assertSame($expected, $actual);
    }

    public static function mutantCommandLineProvider(): iterable
    {
        yield 'without optional arguments' => [
            'coverageTests' => [],
            'mutatedFilePath' => '',
            'mutationHash' => 'hash',
            'mutationOriginalFilePath' => '',
            'extraOptions' => '',
            'expected' => self::createExpectedCommand([
                '/php',
                '/debug.php',
                '--stage',
                'test-framework-mutant',
                '--log',
                '/tmp/infection',
                '--mutationHash',
                'hash',
            ]),
        ];

        yield 'with optional arguments' => [
            'coverageTests' => [TestLocation::forTestMethod('Test::test')],
            'mutatedFilePath' => '/mutant.php',
            'mutationHash' => 'other-hash',
            'mutationOriginalFilePath' => '/source.php',
            'extraOptions' => '--filter=test',
            'expected' => self::createExpectedCommand([
                '/php',
                '/debug.php',
                '--stage',
                'test-framework-mutant',
                '--log',
                '/tmp/infection',
                '--mutationHash',
                'other-hash',
            ]),
        ];
    }

    /**
     * @param string[] $command
     *
     * @return string[]
     */
    private static function createExpectedCommand(array $command): array
    {
        return [
            ...$command,
            '--command',
            base64_encode(json_encode($command, JSON_THROW_ON_ERROR)),
        ];
    }
}
