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

namespace Infection\TestFramework\PhpUnit\Adapter;

use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Infection\TestFramework\HasExtraNodeVisitors;
use Infection\TestFramework\MemoryUsageAware;
use Infection\Visitor\CodeCoverageClassIgnoreVisitor;
use Infection\Visitor\CodeCoverageMethodIgnoreVisitor;

/**
 * @internal
 */
final class PhpUnitAdapter extends AbstractTestFrameworkAdapter implements MemoryUsageAware, HasExtraNodeVisitors
{
    public const JUNIT_FILE_NAME = 'phpunit.junit.xml';

    public function testsPass(string $output): bool
    {
        if (preg_match('/failures!/i', $output)) {
            return false;
        }

        if (preg_match('/errors!/i', $output)) {
            return false;
        }

        // OK (XX tests, YY assertions)
        $isOk = preg_match('/OK\s\(/', $output);

        // "OK, but incomplete, skipped, or risky tests!"
        $isOkWithInfo = preg_match('/OK\s?,/', $output);

        // "Warnings!" - e.g. when deprecated functions are used, but tests pass
        $isWarning = preg_match('/warnings!/i', $output);

        return $isOk || $isOkWithInfo || $isWarning;
    }

    public function getMemoryUsed(string $output): float
    {
        if (preg_match('/Memory: (\d+(?:\.\d+))MB/', $output, $match)) {
            return (float) $match[1];
        }

        return -1;
    }

    public function getMutationsCollectionNodeVisitors(): array
    {
        return [
            100 => new CodeCoverageClassIgnoreVisitor(),
            15 => new CodeCoverageMethodIgnoreVisitor(),
        ];
    }

    public function getName(): string
    {
        return 'PHPUnit';
    }

    public function getInitialTestsFailRecommendations(string $commandLine): string
    {
        $recommendations = parent::getInitialTestsFailRecommendations($commandLine);

        if (version_compare($this->getVersion(), '7.2', '>=')) {
            $recommendations = sprintf(
                "%s\n\n%s\n\n%s",
                "Infection runs the test suite in a RANDOM order. Make sure your tests do not have hidden dependencies.\n\n" .
                'You can add these attributes to `phpunit.xml` to check it: <phpunit executionOrder="random" resolveDependencies="true" ...',
                'If you don\'t want to let Infection run tests in a random order, set the `executionOrder` to some value, for example <phpunit executionOrder="default"',
                parent::getInitialTestsFailRecommendations($commandLine)
            );
        }

        return $recommendations;
    }
}
