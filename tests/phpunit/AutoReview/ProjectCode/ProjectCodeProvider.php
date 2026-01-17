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

use function array_filter;
use const DIRECTORY_SEPARATOR;
use function in_array;
use Infection\CannotBeInstantiated;
use Infection\Command\ConfigureCommand;
use Infection\Command\Git\LoggerFactory;
use Infection\Command\Git\Option\BaseOption;
use Infection\Command\Git\Option\FilterOption;
use Infection\Command\Option\ConfigurationOption;
use Infection\Command\Option\SourceFilterOptions;
use Infection\Config\ConsoleHelper;
use Infection\Config\Guesser\SourceDirGuesser;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Configuration\Schema\SchemaConfigurationFactory;
use Infection\Configuration\Schema\SchemaConfigurationFileLoader;
use Infection\Configuration\Schema\SchemaValidator;
use Infection\Configuration\SourceFilter\FakeSourceFilter;
use Infection\Configuration\SourceFilter\GitDiffFilter;
use Infection\Configuration\SourceFilter\IncompleteGitDiffFilter;
use Infection\Console\Application;
use Infection\Console\OutputFormatter\FormatterName;
use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Console\OutputFormatter\ProgressFormatter;
use Infection\Console\XdebugHandler;
use Infection\Event\Subscriber\DispatchPcntlSignalSubscriber;
use Infection\Event\Subscriber\MutationGeneratingConsoleLoggerSubscriber;
use Infection\Event\Subscriber\NullSubscriber;
use Infection\Event\Subscriber\StopInfectionOnSigintSignalSubscriber;
use Infection\FileSystem\DummyFileSystem;
use Infection\FileSystem\FakeFileSystem;
use Infection\FileSystem\FileSystem;
use Infection\FileSystem\Finder\ConcreteComposerExecutableFinder;
use Infection\FileSystem\Finder\NonExecutableFinder;
use Infection\FileSystem\Finder\TestFrameworkFinder;
use Infection\Framework\OperatingSystem;
use Infection\Logger\Http\StrykerCurlClient;
use Infection\Logger\Http\StrykerDashboardClient;
use Infection\Metrics\MetricsCalculator;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutator\Definition;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use Infection\Mutator\NodeMutationGenerator;
use Infection\PhpParser\Visitor\NameResolverFactory;
use Infection\Process\Runner\IndexedMutantProcessContainer;
use Infection\Resource\Processor\CpuCoresCountProvider;
use Infection\Source\Collector\FakeSourceCollector;
use Infection\Source\Collector\FixedSourceCollector;
use Infection\Source\Collector\GitDiffSourceCollector;
use Infection\Source\Matcher\NullSourceLineMatcher;
use Infection\TestFramework\AdapterInstaller;
use Infection\TestFramework\Coverage\JUnit\TestFileTimeData;
use Infection\TestFramework\Coverage\Locator\FakeLocator;
use Infection\TestFramework\Coverage\Locator\Throwable\InvalidReportSource;
use Infection\TestFramework\Coverage\Locator\Throwable\NoReportFound;
use Infection\TestFramework\Coverage\Locator\Throwable\TooManyReportsFound;
use Infection\TestFramework\MapSourceClassToTestStrategy;
use Infection\TestFramework\PhpUnit\CommandLine\FilterBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder as PhpUnitInitalConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder as PhpUnitMutationConfigBuilder;
use Infection\TestFramework\Tracing\Trace\EmptyTrace;
use Infection\TestFramework\Tracing\Trace\NodeLineRangeData;
use Infection\TestFramework\Tracing\Trace\SourceMethodLineRange;
use Infection\TestFramework\Tracing\Trace\TestLocations;
use Infection\Testing\BaseMutatorTestCase;
use Infection\Testing\MutatorName;
use Infection\Testing\SingletonContainer;
use Infection\Tests\AutoReview\ConcreteClassReflector;
use Infection\Tests\TestingUtility\PHPUnit\DataProviderFactory;
use function iterator_to_array;
use function ltrim;
use function Pipeline\take;
use ReflectionClass;
use function sort;
use const SORT_STRING;
use function sprintf;
use function str_replace;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class ProjectCodeProvider
{
    use CannotBeInstantiated;

    /**
     * This array contains all classes that don't have tests yet, due to legacy
     * reasons. This list should never be added to, only removed from.
     */
    public const NON_TESTED_CONCRETE_CLASSES = [
        AdapterInstaller::class,
        Application::class,
        BaseMutatorTestCase::class,
        BaseOption::class,
        ConcreteComposerExecutableFinder::class,
        ConfigureCommand::class,
        ConfigurationOption::class,
        CpuCoresCountProvider::class,
        DispatchPcntlSignalSubscriber::class,
        DummyFileSystem::class,
        EmptyTrace::class,
        FakeFileSystem::class,
        FakeLocator::class,
        FakeSourceCollector::class,
        FakeSourceFilter::class,
        FileSystem::class,
        FilterOption::class,
        FixedSourceCollector::class,
        SourceFilterOptions::class,
        FormatterName::class,
        GitDiffFilter::class,
        GitDiffSourceCollector::class,
        IncompleteGitDiffFilter::class,
        InvalidReportSource::class,
        LoggerFactory::class,
        Logs::class,
        MapSourceClassToTestStrategy::class, // no need to test 1 const for now
        MutantExecutionResult::class,
        MutationGeneratingConsoleLoggerSubscriber::class,
        MutatorName::class,
        NameResolverFactory::class,
        NodeMutationGenerator::class,
        NoReportFound::class,
        NonExecutableFinder::class,
        NullSourceLineMatcher::class,
        NullSubscriber::class,
        OperatingSystem::class,
        ProgressFormatter::class,
        SchemaConfiguration::class,
        SingletonContainer::class,
        Source::class,
        StopInfectionOnSigintSignalSubscriber::class,
        StrykerCurlClient::class,
        TooManyReportsFound::class,
        XdebugHandler::class,
    ];

    /**
     * This array contains all classes that have tests but for which the test case
     * does not follow the pattern "Acme\Service\Foo" -> "Acme\Tests\FooTest".
     * For example, test cases that are in a child directory.
     */
    public const CONCRETE_CLASSES_WITH_TESTS_IN_DIFFERENT_LOCATION = [
        FilterBuilder::class,
    ];

    /**
     * This array contains all classes that are not extension points, but not final due to legacy
     * reasons. This list should never be added to, only removed from.
     */
    public const NON_FINAL_EXTENSION_CLASSES = [
        ConsoleHelper::class,
        FileSystem::class,
        MetricsCalculator::class,
        PhpUnitInitalConfigBuilder::class,
        PhpUnitMutationConfigBuilder::class,
        SourceDirGuesser::class,
        StrykerDashboardClient::class,
        TestFrameworkFinder::class,
    ];

    /**
     * This array contains all classes that can be extended by our users.
     */
    public const EXTENSION_POINTS = [
        BaseMutatorTestCase::class,
        Definition::class,
        Mutator::class,
        MutatorCategory::class,
        OutputFormatter::class,
        SchemaConfigurationFactory::class,
        SchemaConfigurationFileLoader::class,
        SchemaValidator::class,
    ];

    /**
     * @var string[]|null
     */
    private static ?array $sourceClasses = null;

    /**
     * @var string[]|null
     */
    private static ?array $sourceClassesToCheckForPublicProperties = null;

    /**
     * @var string[]|null
     */
    private static ?array $testClasses = null;

    public static function provideSourceClasses(): iterable
    {
        if (self::$sourceClasses !== null) {
            yield from self::$sourceClasses;

            return;
        }

        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->notName('DummySymfony5FileSystem.php')
            ->notName('DummySymfony6FileSystem.php')
            ->notName('__Name__.php')
            ->notName('__Name__Test.php')
            ->in(__DIR__ . '/../../../../src')
        ;

        self::$sourceClasses = take($finder)
            ->cast(self::castSplFileInfoToFQCN(...))
            ->toList();

        sort(self::$sourceClasses, SORT_STRING);

        yield from self::$sourceClasses;
    }

    public static function sourceClassesProvider(): iterable
    {
        yield from DataProviderFactory::fromIterable(
            self::provideSourceClasses(),
        );
    }

    public static function provideConcreteSourceClasses(): iterable
    {
        yield from ConcreteClassReflector::filterByConcreteClasses(iterator_to_array(
            self::provideSourceClasses(),
            true,
        ));
    }

    /**
     * @return iterable<string, array{class-string}>
     */
    public static function concreteSourceClassesProvider(): iterable
    {
        yield from DataProviderFactory::fromIterable(
            self::provideConcreteSourceClasses(),
        );
    }

    public static function provideSourceClassesToCheckForPublicProperties(): iterable
    {
        if (self::$sourceClassesToCheckForPublicProperties !== null) {
            yield from self::$sourceClassesToCheckForPublicProperties;

            return;
        }

        self::$sourceClassesToCheckForPublicProperties = array_filter(
            iterator_to_array(self::provideSourceClasses(), true),
            static function (string $className): bool {
                $reflectionClass = new ReflectionClass($className);

                return !$reflectionClass->isInterface()
                    && !$reflectionClass->isEnum()
                    && !in_array(
                        $className,
                        [
                            // having public properties on DTO is for performance reasons
                            TestLocations::class,
                            SourceMethodLineRange::class,
                            NodeLineRangeData::class,
                            TestFileTimeData::class,
                            IndexedMutantProcessContainer::class,
                        ],
                        true,
                    )
                ;
            },
        );

        yield from self::$sourceClassesToCheckForPublicProperties;
    }

    public static function sourceClassesToCheckForPublicPropertiesProvider(): iterable
    {
        yield from DataProviderFactory::fromIterable(
            self::provideSourceClassesToCheckForPublicProperties(),
        );
    }

    public static function provideTestClasses(): iterable
    {
        if (self::$testClasses !== null) {
            yield from self::$testClasses;

            return;
        }

        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in(__DIR__ . '/../../../../tests')
            ->notName('DummySymfony5FileSystem.php')
            ->notName('DummySymfony6FileSystem.php')
            ->exclude([
                'autoloaded',
                'benchmark',
                'e2e',
                'Fixtures',
            ])
        ;

        self::$testClasses = take($finder)
            ->cast(self::castTestSplFileInfoToFQCN(...))
            ->toList();

        sort(self::$testClasses, SORT_STRING);

        yield from self::$testClasses;
    }

    // "testClassesProvider" would be more correct but PHPUnit will then detect this method as a
    // test instead of a test provider.
    public static function classesTestProvider(): iterable
    {
        yield from DataProviderFactory::fromIterable(
            self::provideTestClasses(),
        );
    }

    public static function nonTestedConcreteClassesProvider(): iterable
    {
        yield from DataProviderFactory::fromIterable([
            ...self::NON_TESTED_CONCRETE_CLASSES,
            ...self::CONCRETE_CLASSES_WITH_TESTS_IN_DIFFERENT_LOCATION,
        ]);
    }

    public static function nonFinalExtensionClasses(): iterable
    {
        yield from DataProviderFactory::fromIterable(
            self::NON_FINAL_EXTENSION_CLASSES,
        );
    }

    private static function castSplFileInfoToFQCN(SplFileInfo $file): string
    {
        return sprintf(
            '%s\\%s%s%s',
            'Infection',
            str_replace(DIRECTORY_SEPARATOR, '\\', $file->getRelativePath()),
            $file->getRelativePath() !== '' ? '\\' : '',
            $file->getBasename('.' . $file->getExtension()),
        );
    }

    private static function castTestSplFileInfoToFQCN(SplFileInfo $file): string
    {
        $fqcnPart = ltrim(str_replace('phpunit', '', $file->getRelativePath()), DIRECTORY_SEPARATOR);
        $fqcnPart = str_replace(DIRECTORY_SEPARATOR, '\\', $fqcnPart);

        return sprintf(
            'Infection\\Tests\\%s%s%s',
            $fqcnPart,
            $file->getRelativePath() === 'phpunit' ? '' : '\\',
            $file->getBasename('.' . $file->getExtension()),
        );
    }
}
