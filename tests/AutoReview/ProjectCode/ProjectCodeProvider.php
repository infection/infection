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

namespace Infection\Tests\AutoReview\ProjectCode;

use Generator;
use Infection\Command\ConfigureCommand;
use Infection\Command\InfectionCommand;
use Infection\Config\ConsoleHelper;
use Infection\Config\Guesser\SourceDirGuesser;
use Infection\Config\InfectionConfig;
use Infection\Console\Application;
use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Console\OutputFormatter\ProgressFormatter;
use Infection\Console\Util\PhpProcess;
use Infection\Differ\DiffColorizer;
use Infection\Differ\Differ;
use Infection\Finder\ComposerExecutableFinder;
use Infection\Finder\TestFrameworkFinder;
use Infection\Http\BadgeApiClient;
use Infection\Logger\ResultsLoggerTypes;
use Infection\Mutant\MetricsCalculator;
use Infection\Mutator\Util\Mutator;
use Infection\Process\Builder\ProcessBuilder;
use Infection\Process\Listener\MutantCreatingConsoleLoggerSubscriber;
use Infection\Process\Listener\MutationGeneratingConsoleLoggerSubscriber;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\Coverage\CoverageFileData;
use Infection\TestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\Coverage\CoverageMethodData;
use Infection\TestFramework\Coverage\TestFileTimeData;
use Infection\TestFramework\PhpSpec\Config\Builder\InitialConfigBuilder as PhpSpecInitalConfigBuilder;
use Infection\TestFramework\PhpSpec\Config\Builder\MutationConfigBuilder as PhpSpecMutationConfigBuilder;
use Infection\TestFramework\PhpSpec\Config\NoCodeCoverageException;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder as PhpUnitInitalConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder as PhpUnitMutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use Infection\TestFramework\TestFrameworkTypes;
use Infection\Utils\VersionParser;
use Infection\Visitor\MutationsCollectorVisitor;
use Infection\Visitor\ParentConnectorVisitor;
use function iterator_to_array;
use ReflectionClass;
use const SORT_STRING;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class ProjectCodeProvider
{
    /**
     * List of classes which should not have a matching test.
     */
    public const NON_TESTED_CONCRETE_CLASSES = [
        ConfigureCommand::class,
        InfectionCommand::class,
        Application::class,
        ProgressFormatter::class,
        PhpProcess::class,
        ComposerExecutableFinder::class,
        BadgeApiClient::class,
        ResultsLoggerTypes::class,
        MutantCreatingConsoleLoggerSubscriber::class,
        MutationGeneratingConsoleLoggerSubscriber::class,
        MutationTestingRunner::class,
        NoCodeCoverageException::class,
        TestFrameworkTypes::class,
        MutationsCollectorVisitor::class,
        ParentConnectorVisitor::class,
    ];

    /**
     * This array contains all classes that are not extension points, but not final due to legacy
     * reasons. This list should never be added to, only removed from.
     */
    public const NON_FINAL_EXTENSION_CLASSES = [
        ConsoleHelper::class,
        SourceDirGuesser::class,
        InfectionConfig::class,
        DiffColorizer::class,
        Differ::class,
        TestFrameworkFinder::class,
        BadgeApiClient::class,
        MetricsCalculator::class,
        ProcessBuilder::class,
        CodeCoverageData::class,
        PhpSpecInitalConfigBuilder::class,
        PhpUnitInitalConfigBuilder::class,
        PhpSpecMutationConfigBuilder::class,
        PhpUnitMutationConfigBuilder::class,
        CoverageXmlParser::class,
        VersionParser::class,
    ];

    /**
     * This array contains all classes that can be extended by our users.
     */
    public const EXTENSION_POINTS = [
        Mutator::class,
        OutputFormatter::class,
    ];

    private static $sourceClasses;

    private function __construct()
    {
    }

    public static function provideSourceClasses(): Generator
    {
        if (null !== self::$sourceClasses) {
            yield from self::$sourceClasses;
        }

        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in(__DIR__ . '/../../../src')
        ;

        $classes = array_map(
            static function (SplFileInfo $file) {
                return sprintf(
                    '%s\\%s%s%s',
                    'Infection',
                    strtr($file->getRelativePath(), \DIRECTORY_SEPARATOR, '\\'),
                    $file->getRelativePath() ? '\\' : '',
                    $file->getBasename('.' . $file->getExtension())
                );
            },
            iterator_to_array($finder, false)
        );
        sort($classes, SORT_STRING);

        self::$sourceClasses = $classes;

        yield from $classes;
    }

    public static function provideConcreteSourceClasses(): Generator
    {
        $sourceClasses = iterator_to_array(self::provideSourceClasses(), true);

        yield from array_filter(
            $sourceClasses,
            static function (string $className): bool {
                $reflectionClass = new ReflectionClass($className);

                return !$reflectionClass->isInterface()
                    && !$reflectionClass->isAbstract()
                    && !$reflectionClass->isTrait()
                ;
            }
        );
    }

    public static function provideSourceClassesToCheckForPublicProperties(): Generator
    {
        yield from array_filter(
            iterator_to_array(self::provideSourceClasses(), true),
            static function (string $className): bool {
                $reflectionClass = new ReflectionClass($className);

                return !$reflectionClass->isInterface()
                    && !\in_array(
                        $className,
                        [
                            CoverageFileData::class,
                            CoverageLineData::class,
                            CoverageMethodData::class,
                            TestFileTimeData::class,
                        ],
                        true
                    )
                ;
            }
        );
    }

    public static function provideTestClasses(): Generator
    {
        static $classes;

        if (null !== $classes) {
            yield from $classes;
        }

        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in(__DIR__ . '/../../../tests')
            ->notName('Helpers.php')
            ->exclude([
                'Fixtures',
            ])
        ;

        $classes = array_map(
            static function (SplFileInfo $file) {
                return sprintf(
                    'Infection\\Tests\\%s%s%s',
                    strtr($file->getRelativePath(), \DIRECTORY_SEPARATOR, '\\'),
                    $file->getRelativePath() ? '\\' : '',
                    $file->getBasename('.' . $file->getExtension())
                );
            },
            iterator_to_array($finder, false)
        );

        sort($classes, SORT_STRING);

        yield from $classes;
    }
}
