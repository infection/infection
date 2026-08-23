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

namespace Infection\Tests\TestFramework\Tracing;

use Infection\Config\ValueProvider\PCOVDirectoryProvider;
use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\TestFramework\Common\CommandLineBuilder;
use Infection\TestFramework\Common\VersionParser;
use Infection\TestFramework\Config\InitialConfigBuilder;
use Infection\TestFramework\Config\MutationConfigBuilder;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\Tracing\TestFrameworkTracerFactory;
use Infection\TestFramework\Tracing\Tracer;
use Infection\Tests\Fixtures\TestFramework\DummyTestFrameworkAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\PhpExecutableFinder;

#[CoversClass(TestFrameworkTracerFactory::class)]
final class TestFrameworkTracerFactoryTest extends TestCase
{
    public function test_it_uses_phpunit_tracer_for_phpunit_adapter(): void
    {
        $phpUnitTracer = $this->createStub(Tracer::class);
        $fallbackTracer = $this->createStub(Tracer::class);

        $factory = new TestFrameworkTracerFactory(
            $phpUnitTracer,
            $fallbackTracer,
        );

        $this->assertSame(
            $phpUnitTracer,
            $factory->create($this->createPhpUnitAdapter()),
        );
    }

    public function test_it_uses_fallback_tracer_for_other_adapters(): void
    {
        $phpUnitTracer = $this->createStub(Tracer::class);
        $fallbackTracer = $this->createStub(Tracer::class);

        $factory = new TestFrameworkTracerFactory(
            $phpUnitTracer,
            $fallbackTracer,
        );

        $this->assertSame(
            $fallbackTracer,
            $factory->create(new DummyTestFrameworkAdapter()),
        );
    }

    private function createPhpUnitAdapter(): PhpUnitAdapter
    {
        return new PhpUnitAdapter(
            'phpunit',
            '/tmp',
            '/tmp/junit.xml',
            new PCOVDirectoryProvider(['/tmp/src'], ''),
            $this->createStub(InitialConfigBuilder::class),
            $this->createStub(MutationConfigBuilder::class),
            $this->createStub(CommandLineArgumentsAndOptionsBuilder::class),
            $this->createStub(ShellCommandRunner::class),
            new VersionParser(),
            new CommandLineBuilder(new PhpExecutableFinder()),
            '12.5.0',
        );
    }
}
