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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
#[CoversClass(PHPStanAdapter::class)]
final class PHPStanAdapterTest extends TestCase
{
    private PHPStanAdapter $adapter;

    private commandLineBuilder&MockObject $commandLineBuilder;

    private PHPStanMutantExecutionResultFactory&MockObject $mutantExecutionResultFactory;

    protected function setUp(): void
    {
        $this->commandLineBuilder = $this->createMock(CommandLineBuilder::class);
        $this->mutantExecutionResultFactory = $this->createMock(PHPStanMutantExecutionResultFactory::class);

        $this->adapter = new PHPStanAdapter(
            $this->mutantExecutionResultFactory,
            '/path/to/phpstan',
            $this->commandLineBuilder,
            new VersionParser(),
            31.0,
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
            ->with('/path/to/phpstan', [], [])
            ->willReturn(['/usr/bin/php', '/path/to/phpstan'])
        ;

        $this->assertSame(['/usr/bin/php', '/path/to/phpstan'], $this->adapter->getInitialRunCommandLine());
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
}
