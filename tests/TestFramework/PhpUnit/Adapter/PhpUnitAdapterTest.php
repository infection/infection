<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
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

namespace Infection\Tests\TestFramework\PhpUnit\Adapter;

use Infection\Finder\AbstractExecutableFinder;
use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\TestFramework\MemoryUsageAware;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\Utils\VersionParser;
use Mockery;

/**
 * @internal
 */
final class PhpUnitAdapterTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    public function test_it_has_a_name(): void
    {
        $adapter = $this->getAdapter();

        $this->assertSame('PHPUnit', $adapter->getName());
    }

    /**
     * @dataProvider passProvider
     */
    public function test_it_determines_whether_tests_pass_or_not($output, $expectedResult): void
    {
        $adapter = $this->getAdapter();

        $result = $adapter->testsPass($output);

        $this->assertSame($expectedResult, $result);
    }

    public function passProvider()
    {
        return [
            ['OK, but incomplete, skipped, or risky tests!', true],
            ['OK (5 tests, 3 assertions)', true],
            ['FAILURES!', false],
            ['ERRORS!', false],
        ];
    }

    public function test_it_conforms_to_memory_usage_aware(): void
    {
        $adapter = $this->getAdapter();
        $this->assertInstanceOf(MemoryUsageAware::class, $adapter);
    }

    /**
     * @dataProvider memoryReportProvider
     */
    public function test_it_determines_used_memory_amount($output, $expectedResult): void
    {
        $adapter = $this->getAdapter();

        $result = $adapter->getMemoryUsed($output);

        $this->assertSame($expectedResult, $result);
    }

    public function memoryReportProvider()
    {
        return [
            ['Memory: 8.00MB', 8.0],
            ['Memory: 68.00MB', 68.0],
            ['Time: 2.51 seconds', -1.0],
        ];
    }

    private function getAdapter(): PhpUnitAdapter
    {
        $executableFined = Mockery::mock(AbstractExecutableFinder::class);
        $initialConfigBuilder = Mockery::mock(InitialConfigBuilder::class);
        $mutationConfigBuilder = Mockery::mock(MutationConfigBuilder::class);
        $cliArgumentsBuilder = Mockery::mock(CommandLineArgumentsAndOptionsBuilder::class);
        $versionParser = Mockery::mock(VersionParser::class);

        return new PhpUnitAdapter(
            $executableFined,
            $initialConfigBuilder,
            $mutationConfigBuilder,
            $cliArgumentsBuilder,
            $versionParser
        );
    }
}
