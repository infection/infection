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

namespace Infection\Tests\AutoReview;

use Infection\Command\ConfigureCommand;
use Infection\Command\InfectionCommand;
use Infection\Command\SelfUpdateCommand;
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
use Infection\StreamWrapper\IncludeInterceptor;
use Infection\TestFramework\Coverage\CodeCoverageData;
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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 *
 * @group auto-review
 *
 * @coversNothing
 *
 * This class is responsible for testing that our code base adheres to certain rules,
 * e.g. 'All classes that aren't intended to be used by users should be marked internal'
 *
 * The goal is to reduce pr reviews about style issues that can't be automatically fixed.
 * All test failures should be clear in meaning, to help new contributors.
 */
final class ProjectCodeTest extends TestCase
{
    /**
     * This array contains all classes that can be extended by our users.
     *
     * @var string[]
     */
    private static $extensionPoints = [
        Mutator::class,
        OutputFormatter::class,
    ];

    /**
     * This array contains all classes that are not extension points, but not final due to legacy reasons.
     * This list should never be added to, only removed from.
     *
     * @var string[]
     */
    private static $nonFinalNonExtensionClasses = [
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
     * @var string[]
     */
    private static $nonTestedConcreteClasses = [
        ConfigureCommand::class,
        InfectionCommand::class,
        SelfUpdateCommand::class,
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

    public function test_infection_bin_is_executable(): void
    {
        if (stripos(PHP_OS, 'WIN') === 0) {
            $this->markTestSkipped('Unable to check if the file is executable on windows.');
        }

        $infectionFile = __DIR__ . '/../../bin/infection';
        $this->assertFileExists($infectionFile);
        $this->assertTrue(is_executable($infectionFile));
    }

    /**
     * @dataProvider providesSourceClasses
     *
     * @param string $className
     */
    public function test_src_class_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className) || interface_exists($className) || trait_exists($className),
            sprintf(
                'The "%s" class was picked up by the source files finder, but it is not a class, interface or trait. ' .
                'Please check for typos in the class name. Or exclude the file if in the ProjectCodeTest if it is not a class.',
                $className
            )
        );
    }

    /**
     * @dataProvider providesTestClassCases
     *
     * @param string $className
     */
    public function test_test_class_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className) || interface_exists($className) || trait_exists($className),
            sprintf(
                'The "%s" class was picked up by the test files finder, but it is not a class, interface or trait. ' .
                'Please check for typos in the class name. Or exclude the file if in the ProjectCodeTest if it is not a class.',
                $className
            )
        );
    }

    /**
     * @dataProvider provideConcreteSourceClasses
     *
     * @param string $className
     */
    public function test_all_concrete_classes_have_tests(string $className): void
    {
        $testClass = preg_replace('/Infection/', 'Infection\\Tests', $className, 1) . 'Test';

        if (\in_array($className, self::$nonTestedConcreteClasses)) {
            $this->assertFalse(class_exists($testClass),
                sprintf(
                    'Class "%s" has a corresponding unit test "%s", and can be removed from the non tested class list',
                    $className,
                    $testClass
                )
            );
            $this->markTestSkipped(sprintf(
                'Class "%s" does not have a corresponding unit test yet, you can improve this by adding one',
                $className
            ));
        }
        $this->assertTrue(class_exists($testClass),
            sprintf(
                'Class "%s" doest not have a corresponding unit test "%s", please add one',
                $className,
                $testClass
            )
        );
    }

    /**
     * @dataProvider provideNonTestedConcreteClasses
     *
     * @param string $className
     */
    public function test_non_tested_concrete_class_list_is_valid(string $className): void
    {
        $this->assertTrue(class_exists($className),
            sprintf(
                'Class "%s" no longer exists, please remove it from the list of non tested classes',
                $className
            )
        );
    }

    /**
     * @dataProvider providesSourceClasses
     *
     * @param string $className
     */
    public function test_non_extension_points_are_internal(string $className): void
    {
        $rc = new \ReflectionClass($className);
        $docBlock = $rc->getDocComment();

        if (\in_array($className, self::$extensionPoints)) {
            if ($docBlock === false) {
                $this->markTestSkipped(
                    sprintf(
                        'The "%s" class is an extension point, but does not have a doc-block.' .
                        'Consider adding a doc-block to improve usability.',
                        $className
                    )
                );
            }
            $this->assertNotContains(
                '@internal',
                $docBlock,
                sprintf(
                    'The "%s" class is an extension point, and should not be marked as internal.',
                    $className
                )
            );

            return;
        }

        $this->assertInternalType(
            'string',
            $docBlock,
            sprintf(
                'The "%s" class is not an extension point, and should be marked as internal.',
                $className
            )
        );
        $this->assertContains(
            '@internal',
            $docBlock,
            sprintf(
                'The "%s" class is not an extension point, and should be marked as internal.',
                $className
            )
        );
    }

    /**
     * @dataProvider providesSourceClasses
     *
     * @param string $className
     */
    public function test_non_extension_points_are_trait_interface_abstract_or_final(string $className): void
    {
        $rc = new \ReflectionClass($className);

        if (\in_array($className, self::$nonFinalNonExtensionClasses)) {
            $this->addToAssertionCount(1);

            return;
        }
        $this->assertTrue(
            $rc->isTrait() || $rc->isInterface() || $rc->isAbstract() || $rc->isFinal(),
            sprintf('Source class "%s" should be trait, abstract or final.', $className)
        );
    }

    /**
     * @dataProvider provideNonFinalNonExtensionClasses
     *
     * @param string $className
     */
    public function test_non_final_non_extension_list_is_valid(string $className): void
    {
        $rc = new \ReflectionClass($className);

        $this->assertTrue(
            !$rc->isTrait() && !$rc->isInterface() && !$rc->isAbstract() && !$rc->isFinal(),
            sprintf(
                'Source class "%s" is a trait, interface, abstract or final class,' .
                ' and should be removed from the nonFinalNonExtensionClasses list.',
                $className
            )
        );
    }

    /**
     * @dataProvider providesSourceClasses
     *
     * @param string $className
     */
    public function test_src_classes_do_not_expose_public_properties(string $className): void
    {
        $rc = new \ReflectionClass($className);

        $properties = $rc->getProperties(\ReflectionProperty::IS_PUBLIC);

        if ($className === IncludeInterceptor::class) {
            // The IncludeInterceptor needs 1 public property: $context
            // @see https://secure.php.net/manual/en/class.streamwrapper.php
            $this->assertCount(
                1,
                $properties,
                sprintf(
                    'The "%s" class must have exactly 1 public property as it is a streamwrapper. ' .
                    'If this has changed due to recent php developments, consider updating this test.',
                    $className
                )
            );
            $this->assertSame(
                'context',
                $properties[0]->getName(),
                sprintf(
                    'The "%s" class must have exactly 1 public property named context. ' .
                    'If this has changed due to recent php developments, consider updating this test.',
                    $className
                )
            );

            return;
        }

        /*
         * We should consider only properties belonging to our classes, but not to foreign classes
         * we're exteding from. E.g. we can't change Symfony\Component\Process\Process to not have
         * a public propery it has.
         */
        $properties = array_filter($properties, function (\ReflectionProperty $property) use ($className) {
            return $property->class === $className;
        });

        $this->assertCount(
            0,
            $properties,
            sprintf(
                'Class "%s" should not declare public properties, ' .
                "if it has properties that need to be accessed, consider getters and setters instead. \nViolations:\n%s",
                $className,
                implode("\n", array_map(static function ($item) {
                    return " * ${item}";
                }, $properties))
            )
        );
    }

    /**
     * @dataProvider providesTestClassCases
     *
     * @param string $className
     */
    public function test_all_test_classes_are_trait_abstract_or_final(string $className): void
    {
        $rc = new \ReflectionClass($className);

        $this->assertTrue(
            $rc->isTrait() || $rc->isAbstract() || $rc->isFinal(),
            sprintf('Test class "%s" should be trait, abstract or final.', $className)
        );
    }

    /**
     * @dataProvider providesTestClassCases
     *
     * @param string $className
     */
    public function test_all_test_classes_are_marked_internal(string $className): void
    {
        $rc = new \ReflectionClass($className);
        $docBlock = $rc->getDocComment();

        $this->assertInternalType(
            'string',
            $docBlock,
            sprintf(
                'Test class  "%s" must be marked internal.',
                $className
            )
        );
        $this->assertContains(
            '@internal',
            $rc->getDocComment(),
            sprintf(
                'Test class  "%s" must be marked internal.',
                $className
            )
        );
    }

    public function providesSourceClasses(): array
    {
        return array_map(
            static function ($item) {
                return [$item];
            },
            $this->getSrcClasses()
        );
    }

    public function provideConcreteSourceClasses(): array
    {
        return array_map(
            static function ($item) {
                return [$item];
            },
            $this->getConcreteSrcClasses()
        );
    }

    public function providesTestClassCases(): array
    {
        return array_map(
            static function ($item) {
                return [$item];
            },
            $this->getTestClasses()
        );
    }

    public function provideNonFinalNonExtensionClasses(): array
    {
        return array_map(
            static function ($item) {
                return [$item];
            },
            self::$nonFinalNonExtensionClasses
        );
    }

    public function provideNonTestedConcreteClasses(): array
    {
        return array_map(
            static function ($item) {
                return [$item];
            },
            self::$nonTestedConcreteClasses
        );
    }

    private function getSrcClasses(): array
    {
        static $classes;

        if (null !== $classes) {
            return $classes;
        }

        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in(__DIR__ . '/../../src')
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
        sort($classes);

        return $classes;
    }

    private function getConcreteSrcClasses(): array
    {
        return array_filter($this->getSrcClasses(),
            function ($class) {
                $rc = new \ReflectionClass($class);

                return !$rc->isInterface() && !$rc->isAbstract() && !$rc->isTrait();
            }
        );
    }

    private function getTestClasses(): array
    {
        static $classes;

        if (null !== $classes) {
            return $classes;
        }

        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in(__DIR__ . '/..')
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
        sort($classes);

        return $classes;
    }
}
