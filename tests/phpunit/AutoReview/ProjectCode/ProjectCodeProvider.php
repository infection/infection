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

use const DIRECTORY_SEPARATOR;
use Generator;
use function in_array;
use Infection\Command\ConfigureCommand;
use Infection\Command\InfectionCommand;
use Infection\Config\ConsoleHelper;
use Infection\Config\Guesser\SourceDirGuesser;
use Infection\Configuration\Schema\SchemaConfigurationFactory;
use Infection\Configuration\Schema\SchemaConfigurationFileLoader;
use Infection\Configuration\Schema\SchemaValidator;
use Infection\Console\Application;
use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Console\OutputFormatter\ProgressFormatter;
use Infection\Console\Util\PhpProcess;
use Infection\Differ\DiffColorizer;
use Infection\Differ\Differ;
use Infection\Engine;
use Infection\Finder\ComposerExecutableFinder;
use Infection\Finder\FilterableFinder;
use Infection\Finder\TestFrameworkFinder;
use Infection\Http\BadgeApiClient;
use Infection\Logger\ResultsLoggerTypes;
use Infection\Mutant\MetricsCalculator;
use Infection\Mutator\NodeMutationGenerator;
use Infection\Mutator\Util\Mutator;
use Infection\Process\Builder\InitialTestRunProcessBuilder;
use Infection\Process\Listener\MutantCreatingConsoleLoggerSubscriber;
use Infection\Process\Listener\MutationGeneratingConsoleLoggerSubscriber;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\TestFramework\Coverage\CoverageFileData;
use Infection\TestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\Coverage\MethodLocationData;
use Infection\TestFramework\Coverage\NodeLineRangeData;
use Infection\TestFramework\Coverage\TestFileTimeData;
use Infection\TestFramework\PhpSpec\Config\Builder\InitialConfigBuilder as PhpSpecInitalConfigBuilder;
use Infection\TestFramework\PhpSpec\Config\Builder\MutationConfigBuilder as PhpSpecMutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder as PhpUnitInitalConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder as PhpUnitMutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use Infection\TestFramework\TestFrameworkTypes;
use function Infection\Tests\generator_to_phpunit_data_provider;
use Infection\Utils\VersionParser;
use function iterator_to_array;
use ReflectionClass;
use const SORT_STRING;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class ProjectCodeProvider
{
    /**
     * This array contains all classes that don't have tests yet, due to legacy
     * reasons. This list should never be added to, only removed from.
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
        TestFrameworkTypes::class,
        NodeMutationGenerator::class,
        FilterableFinder::class,
        Engine::class,
    ];

    /**
     * This array contains all classes that are not extension points, but not final due to legacy
     * reasons. This list should never be added to, only removed from.
     */
    public const NON_FINAL_EXTENSION_CLASSES = [
        ConsoleHelper::class,
        SourceDirGuesser::class,
        DiffColorizer::class,
        Differ::class,
        TestFrameworkFinder::class,
        BadgeApiClient::class,
        MetricsCalculator::class,
        InitialTestRunProcessBuilder::class,
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
        SchemaConfigurationFactory::class,
        SchemaConfigurationFileLoader::class,
        SchemaValidator::class,
    ];

    /**
     * @var string[]|null
     */
    private static $sourceClasses;

    /**
     * @var string[]|null
     */
    private static $testClasses;

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
            ->in(__DIR__ . '/../../../../src')
        ;

        $classes = array_map(
            static function (SplFileInfo $file) {
                return sprintf(
                    '%s\\%s%s%s',
                    'Infection',
                    str_replace(DIRECTORY_SEPARATOR, '\\', $file->getRelativePath()),
                    $file->getRelativePath() ? '\\' : '',
                    $file->getBasename('.' . $file->getExtension())
                );
            },
            iterator_to_array($finder, false)
        );
        sort($classes, SORT_STRING);

        self::$sourceClasses = $classes;

        yield from self::$sourceClasses;
    }

    public static function sourceClassesProvider(): Generator
    {
        yield from generator_to_phpunit_data_provider(
            self::provideSourceClasses()
        );
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

    public static function concreteSourceClassesProvider(): Generator
    {
        yield from generator_to_phpunit_data_provider(
            self::provideConcreteSourceClasses()
        );
    }

    public static function provideSourceClassesToCheckForPublicProperties(): Generator
    {
        yield from array_filter(
            iterator_to_array(self::provideSourceClasses(), true),
            static function (string $className): bool {
                $reflectionClass = new ReflectionClass($className);

                return !$reflectionClass->isInterface()
                    && !in_array(
                        $className,
                        [
                            CoverageFileData::class,
                            CoverageLineData::class,
                            MethodLocationData::class,
                            NodeLineRangeData::class,
                            TestFileTimeData::class,
                        ],
                        true
                    )
                ;
            }
        );
    }

    public static function sourceClassesToCheckForPublicPropertiesProvider(): Generator
    {
        yield from generator_to_phpunit_data_provider(
            self::provideSourceClassesToCheckForPublicProperties()
        );
    }

    public static function provideTestClasses(): Generator
    {
        if (null !== self::$testClasses) {
            yield from self::$testClasses;
        }

        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in(__DIR__ . '/../../../../tests')
            ->notName('Helpers.php')
            ->exclude([
                'e2e',
                'Fixtures',
            ])
        ;

        $classes = array_map(
            static function (SplFileInfo $file) {
                $fqcnPart = ltrim(str_replace('phpunit', '', $file->getRelativePath()), DIRECTORY_SEPARATOR);
                $fqcnPart = str_replace(DIRECTORY_SEPARATOR, '\\', $fqcnPart);

                return sprintf(
                    'Infection\\Tests\\%s%s%s',
                    $fqcnPart,
                    $file->getRelativePath() === 'phpunit' ? '' : '\\',
                    $file->getBasename('.' . $file->getExtension())
                );
            },
            iterator_to_array($finder, false)
        );

        sort($classes, SORT_STRING);

        self::$testClasses = $classes;

        yield from self::$testClasses;
    }

    // "testClassesProvider" would be more correct but PHPUnit will then detect this method as a
    // test instead of a test provider.
    public static function classesTestProvider(): Generator
    {
        yield from generator_to_phpunit_data_provider(
            self::provideTestClasses()
        );
    }

    public static function nonTestedConcreteClassesProvider(): Generator
    {
        yield from generator_to_phpunit_data_provider(
            self::NON_TESTED_CONCRETE_CLASSES
        );
    }

    public static function nonFinalExtensionClasses(): Generator
    {
        yield from generator_to_phpunit_data_provider(
            self::NON_FINAL_EXTENSION_CLASSES
        );
    }
}
