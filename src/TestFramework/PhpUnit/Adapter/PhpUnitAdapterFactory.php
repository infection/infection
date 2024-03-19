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

namespace Infection\TestFramework\PhpUnit\Adapter;

use function array_map;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\AbstractTestFramework\TestFrameworkAdapterFactory;
use Infection\Config\ValueProvider\PCOVDirectoryProvider;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\Coverage\JUnit\JUnitTestCaseSorter;
use Infection\TestFramework\PhpUnit\CommandLine\ArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationVersionProvider;
use Infection\TestFramework\VersionParser;
use function Safe\file_get_contents;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class PhpUnitAdapterFactory implements TestFrameworkAdapterFactory
{
    /**
     * @param string[] $sourceDirectories
     * @param list<SplFileInfo> $filteredSourceFilesToMutate
     */
    public static function create(
        string $testFrameworkExecutable,
        string $tmpDir,
        string $testFrameworkConfigPath,
        ?string $testFrameworkConfigDir,
        string $jUnitFilePath,
        string $projectDir,
        array $sourceDirectories,
        bool $skipCoverage,
        bool $executeOnlyCoveringTestCases = false,
        array $filteredSourceFilesToMutate = [],
        ?string $mapSourceClassToTestStrategy = null,
    ): TestFrameworkAdapter {
        Assert::string($testFrameworkConfigDir, 'Config dir is not allowed to be `null` for the Pest adapter');

        $testFrameworkConfigContent = file_get_contents($testFrameworkConfigPath);

        $configManipulator = new XmlConfigurationManipulator(
            new PathReplacer(
                new Filesystem(),
                $testFrameworkConfigDir,
            ),
            $testFrameworkConfigDir,
        );

        return new PhpUnitAdapter(
            $testFrameworkExecutable,
            $tmpDir,
            $jUnitFilePath,
            new PCOVDirectoryProvider(),
            new InitialConfigBuilder(
                $tmpDir,
                $testFrameworkConfigContent,
                $configManipulator,
                new XmlConfigurationVersionProvider(),
                $sourceDirectories,
                array_map(
                    static fn (SplFileInfo $fileInfo): string => $fileInfo->getRealPath(),
                    $filteredSourceFilesToMutate,
                ),
            ),
            new MutationConfigBuilder(
                $tmpDir,
                $testFrameworkConfigContent,
                $configManipulator,
                $projectDir,
                new JUnitTestCaseSorter(),
            ),
            new ArgumentsAndOptionsBuilder(
                $executeOnlyCoveringTestCases,
                $filteredSourceFilesToMutate,
                $mapSourceClassToTestStrategy,
            ),
            new VersionParser(),
            new CommandLineBuilder(),
        );
    }

    public static function getAdapterName(): string
    {
        return 'phpunit';
    }

    public static function getExecutableName(): string
    {
        return 'phpunit';
    }
}
