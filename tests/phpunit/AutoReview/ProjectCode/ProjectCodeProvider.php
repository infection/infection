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
use Infection\Configuration\Schema\SchemaConfigurationFactory;
use Infection\Configuration\Schema\SchemaConfigurationFileLoader;
use Infection\Configuration\Schema\SchemaValidator;
use Infection\Logger\MutationAnalysis\MutationAnalysisLogger;
use Infection\Mutator\Definition;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use Infection\Process\Runner\IndexedMutantProcessContainer;
use Infection\TestFramework\Coverage\JUnit\TestFileTimeData;
use Infection\TestFramework\Tracing\Trace\NodeLineRangeData;
use Infection\TestFramework\Tracing\Trace\SourceMethodLineRange;
use Infection\TestFramework\Tracing\Trace\TestLocations;
use Infection\Testing\BaseMutatorTestCase;
use Infection\Tests\TestingUtility\PHPUnit\DataProviderFactory;
use function iterator_to_array;
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
     * This array contains all classes that can be extended by our users.
     */
    public const array EXTENSION_POINTS = [
        BaseMutatorTestCase::class,
        Definition::class,
        Mutator::class,
        MutationAnalysisLogger::class,
        MutatorCategory::class,
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
}
