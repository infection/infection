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

namespace Infection\Tests\Testing\Debug;

use Infection\Testing\Debug\DebugTestFrameworkAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function sprintf;
use function str_starts_with;
use function strlen;
use function substr;

#[CoversClass(DebugTestFrameworkAdapter::class)]
#[Group('integration')]
final class DebugTestFrameworkAdapterTest extends TestCase
{
    public function test_it_describes_successful_debug_runs(): void
    {
        $adapter = new DebugTestFrameworkAdapter('/tmp/infection');

        $this->assertTrue($adapter->testsPass('DEBUG_TEST_FRAMEWORK_PASSED'));
        $this->assertSame(16., $adapter->getMemoryUsed('Memory: 16.00 MB'));
        $this->assertSame('test-framework-initial', $this->option($adapter->getInitialTestRunCommandLine('', [], false), 'stage'));
        $this->assertSame('test-framework-mutant', $this->option($adapter->getMutantCommandLine([], '', 'hash', '', ''), 'stage'));
    }

    /** @param string[] $command */
    private function option(array $command, string $name): string
    {
        foreach ($command as $argument) {
            if (str_starts_with($argument, $name . '=')) {
                return substr($argument, strlen($name) + 1);
            }
        }

        $this->fail(sprintf('Option "%s" was not found.', $name));
    }
}
