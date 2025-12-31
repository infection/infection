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

namespace Infection\Tests\StaticAnalysis\PHPStan\Adapter;

use Infection\StaticAnalysis\PHPStan\Adapter\PHPStanAdapter;
use Infection\StaticAnalysis\PHPStan\Mutant\PHPStanMutantExecutionResultFactory;
use Infection\StaticAnalysis\PHPStan\Process\PHPStanMutantProcessFactory;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\VersionParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function sprintf;
use Symfony\Component\Filesystem\Filesystem;

#[Group('integration')]
#[CoversClass(PHPStanAdapter::class)]
final class PHPStanAdapterTest extends TestCase
{
    private PHPStanAdapter $adapter;

    private commandLineBuilder&MockObject $commandLineBuilder;

    private PHPStanMutantExecutionResultFactory&MockObject $mutantExecutionResultFactory;

    private Filesystem&MockObject $fileSystem;

    protected function setUp(): void
    {
        $this->commandLineBuilder = $this->createMock(CommandLineBuilder::class);
        $this->mutantExecutionResultFactory = $this->createMock(PHPStanMutantExecutionResultFactory::class);
        $this->fileSystem = $this->createMock(Filesystem::class);

        $this->adapter = new PHPStanAdapter(
            $this->fileSystem,
            $this->mutantExecutionResultFactory,
            '/path/to/phpstan-config-path',
            '/path/to/phpstan',
            $this->commandLineBuilder,
            new VersionParser(),
            31.0,
            '/tmp',
            [],
            '9.0',
        );
    }

    public function test_it_has_a_name(): void
    {
        $this->assertSame('PHPStan', $this->adapter->getName());
    }

    public function test_it_builds_initial_run_command_line(): void
    {
        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpstan', [], ['--configuration=/path/to/phpstan-config-path'])
            ->willReturn(['/usr/bin/php', '/path/to/phpstan', '--configuration=/path/to/phpstan-config-path'])
        ;

        $this->assertSame([
            '/usr/bin/php',
            '/path/to/phpstan',
            '--configuration=/path/to/phpstan-config-path',
        ], $this->adapter->getInitialRunCommandLine());
    }

    public function test_it_builds_initial_run_command_line_with_single_option(): void
    {
        $adapter = new PHPStanAdapter(
            $this->fileSystem,
            $this->mutantExecutionResultFactory,
            '/path/to/phpstan-config-path',
            '/path/to/phpstan',
            $this->commandLineBuilder,
            new VersionParser(),
            31.0,
            '/tmp',
            ['--memory-limit=1G'],
            '9.0',
        );

        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpstan', [], [
                '--configuration=/path/to/phpstan-config-path',
                '--memory-limit=1G',
            ])
            ->willReturn(['/usr/bin/php', '/path/to/phpstan', '--configuration=/path/to/phpstan-config-path', '--memory-limit=1G'])
        ;

        $this->assertSame([
            '/usr/bin/php',
            '/path/to/phpstan',
            '--configuration=/path/to/phpstan-config-path',
            '--memory-limit=1G',
        ], $adapter->getInitialRunCommandLine());
    }

    public function test_it_builds_initial_run_command_line_with_multiple_options(): void
    {
        $adapter = new PHPStanAdapter(
            $this->fileSystem,
            $this->mutantExecutionResultFactory,
            '/path/to/phpstan-config-path',
            '/path/to/phpstan',
            $this->commandLineBuilder,
            new VersionParser(),
            31.0,
            '/tmp',
            ['--memory-limit=-1', '--no-progress'],
            '9.0',
        );

        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpstan', [], [
                '--configuration=/path/to/phpstan-config-path',
                '--memory-limit=-1',
                '--no-progress',
            ])
            ->willReturn(['/usr/bin/php', '/path/to/phpstan', '--configuration=/path/to/phpstan-config-path', '--memory-limit=-1', '--no-progress'])
        ;

        $this->assertSame([
            '/usr/bin/php',
            '/path/to/phpstan',
            '--configuration=/path/to/phpstan-config-path',
            '--memory-limit=-1',
            '--no-progress',
        ], $adapter->getInitialRunCommandLine());
    }

    public function test_it_builds_initial_run_command_line_with_complex_options(): void
    {
        $adapter = new PHPStanAdapter(
            $this->fileSystem,
            $this->mutantExecutionResultFactory,
            '/path/to/phpstan-config-path',
            '/path/to/phpstan',
            $this->commandLineBuilder,
            new VersionParser(),
            31.0,
            '/tmp',
            ['--memory-limit=2G', '--level=max', '--no-progress'],
            '9.0',
        );

        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpstan', [], [
                '--configuration=/path/to/phpstan-config-path',
                '--memory-limit=2G',
                '--level=max',
                '--no-progress',
            ])
            ->willReturn(['/usr/bin/php', '/path/to/phpstan', '--configuration=/path/to/phpstan-config-path', '--memory-limit=2G', '--level=max', '--no-progress'])
        ;

        $this->assertSame([
            '/usr/bin/php',
            '/path/to/phpstan',
            '--configuration=/path/to/phpstan-config-path',
            '--memory-limit=2G',
            '--level=max',
            '--no-progress',
        ], $adapter->getInitialRunCommandLine());
    }

    public function test_it_returns_version(): void
    {
        $this->assertSame('9.0', $this->adapter->getVersion());
    }

    public function test_it_creates_mutant_process_creator(): void
    {
        $this->assertInstanceOf(
            PHPStanMutantProcessFactory::class,
            $this->adapter->createMutantProcessFactory(),
        );
    }

    #[DataProvider('provideValidVersions')]
    public function test_it_accepts_valid_versions(string $version): void
    {
        $adapter = new PHPStanAdapter(
            $this->fileSystem,
            $this->mutantExecutionResultFactory,
            '/path/to/phpstan-config-path',
            '/path/to/phpstan',
            $this->commandLineBuilder,
            new VersionParser(),
            31.0,
            '/tmp',
            ['--memory-limit=-1'],
            $version,
        );

        // This should not throw an exception
        $adapter->assertMinimumVersionSatisfied();

        $this->addToAssertionCount(1);
    }

    #[DataProvider('provideInvalidVersions')]
    public function test_it_rejects_invalid_versions(string $version): void
    {
        $adapter = new PHPStanAdapter(
            $this->fileSystem,
            $this->mutantExecutionResultFactory,
            '/path/to/phpstan-config-path',
            '/path/to/phpstan',
            $this->commandLineBuilder,
            new VersionParser(),
            31.0,
            '/tmp',
            [],
            $version,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'Infection requires PHPStan version >=1.12.27 or >=2.1.17, but "%s" is installed.',
            $version,
        ));

        $adapter->assertMinimumVersionSatisfied();
    }

    public static function provideValidVersions(): iterable
    {
        yield 'major version 3' => ['3.0.0'];

        yield 'major version 2 with valid patch' => ['2.1.17'];

        yield 'major version 2 with higher minor' => ['2.2.0'];

        yield 'major version 1 with valid patch' => ['1.12.27'];

        yield 'major version 1 with valid patch 2' => ['1.12.28'];

        yield 'major version 1 with higher minor' => ['1.13.0'];

        yield 'dev version 1.12.x' => ['1.12.x-dev@asgar3'];

        yield 'dev version 1.13.x' => ['1.13.x-dev@cfa0299'];

        yield 'dev version 2.1.x' => ['2.1.x-dev@cfa0299'];

        yield 'dev version 2.2.x' => ['2.2.x-dev@cfa0299'];

        yield 'PHPStan-src dev' => ['dev-648dbd911cef28707338fe5c25875d50e7875391@648dbd9'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideInvalidVersions(): iterable
    {
        yield 'major version 2 with too low minor' => ['2.0.17'];

        yield 'major version 2 with too low patch' => ['2.1.1'];

        yield 'major version 2 with too low minor and patch' => ['2.0.0'];

        yield 'major version 1 with too low patch' => ['1.12.26'];

        yield 'major version 1 with too low minor and patch' => ['1.11.0'];

        yield 'major version 0' => ['0.12.0'];

        yield 'dev version 1.0.x' => ['1.0.x-dev@cfa0299'];

        yield 'dev version 2.0.x' => ['2.0.x-dev@cfa0299'];
    }
}
