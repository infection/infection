<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\AutoReview;

use Infection\Config\ConsoleHelper;
use Infection\Config\Guesser\SourceDirGuesser;
use Infection\Config\InfectionConfig;
use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Differ\DiffColorizer;
use Infection\Differ\Differ;
use Infection\EventDispatcher\EventDispatcher;
use Infection\Finder\TestFrameworkFinder;
use Infection\Http\BadgeApiClient;
use Infection\Mutant\MetricsCalculator;
use Infection\Mutator\Util\Mutator;
use Infection\Process\Builder\ProcessBuilder;
use Infection\StreamWrapper\IncludeInterceptor;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\PhpSpec\Config\Builder\InitialConfigBuilder as PhpSpecInitalConfigBuilder;
use Infection\TestFramework\PhpSpec\Config\Builder\MutationConfigBuilder as PhpSpecMutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder as PhpUnitInitalConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder as PhpUnitMutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use Infection\Utils\VersionParser;
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
        EventDispatcher::class,
        TestFrameworkFinder::class,
        BadgeApiClient::class,
        MetricsCalculator::class,
        ProcessBuilder::class,
        CodeCoverageData::class,
        PhpSpecInitalConfigBuilder::class,
        PhpUnitInitalConfigBuilder::class,
        PhpSpecMutationConfigBuilder::class,
        PhpUnitMutationConfigBuilder::class,
        XmlConfigurationHelper::class,
        CoverageXmlParser::class,
        VersionParser::class,
    ];

    public function test_infection_bin_is_executable()
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
    public function test_src_class_provider_is_valid(string $className)
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
    public function test_test_class_provider_is_valid(string $className)
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
     * @dataProvider providesSourceClasses
     *
     * @param string $className
     */
    public function test_non_extension_points_are_internal(string $className)
    {
        $rc = new \ReflectionClass($className);
        $docBlock = $rc->getDocComment();

        if (in_array($className, self::$extensionPoints)) {
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

        $this->assertTrue(
            is_string($docBlock),
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
    public function test_non_extension_points_are_trait_interface_abstract_or_final(string $className)
    {
        $rc = new \ReflectionClass($className);

        if (in_array($className, self::$nonFinalNonExtensionClasses)) {
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
    public function test_non_final_non_extension_list_is_valid(string $className)
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
    public function test_src_classes_do_not_expose_public_properties(string $className)
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
            return $property->class == $className;
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
    public function test_all_test_classes_are_trait_abstract_or_final(string $className)
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
    public function test_all_test_classes_are_marked_internal(string $className)
    {
        $rc = new \ReflectionClass($className);
        $docBlock = $rc->getDocComment();

        $this->assertTrue(
            is_string($docBlock),
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

    public function providesSourceClasses()
    {
        return array_map(
            static function ($item) {
                return [$item];
            },
            $this->getSrcClasses()
        );
    }

    public function providesTestClassCases()
    {
        return array_map(
            static function ($item) {
                return [$item];
            },
            $this->getTestClasses()
        );
    }

    public function provideNonFinalNonExtensionClasses()
    {
        return array_map(
            static function ($item) {
                return [$item];
            },
            self::$nonFinalNonExtensionClasses
        );
    }

    private function getSrcClasses()
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
                    strtr($file->getRelativePath(), DIRECTORY_SEPARATOR, '\\'),
                    $file->getRelativePath() ? '\\' : '',
                    $file->getBasename('.' . $file->getExtension())
                );
            },
            iterator_to_array($finder, false)
        );
        sort($classes);

        return $classes;
    }

    private function getTestClasses()
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
                    strtr($file->getRelativePath(), DIRECTORY_SEPARATOR, '\\'),
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
