<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\AutoReview;

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
 * This class is responsible for testing that all Mutator classes adhere to certain rules
 * e.g. 'Mutators shouldn't declare any public methods`
 */
final class MutatorTest extends TestCase
{
    /**
     * @dataProvider providesMutatorClasses
     *
     * @param string $className
     */
    public function test_mutator_class_provider_is_valid(string $className)
    {
        $this->assertTrue(
            class_exists($className) || interface_exists($className) || trait_exists($className),
            sprintf(
                'The "%s" class was picked up by the Mutator files finder, but it is not a class, interface or trait. ' .
                'Please check for typos in the class name. Or exclude the file if in the ProjectCodeTest if it is not a class.',
                $className
            )
        );
    }

    /**
     * @dataProvider providesMutatorClasses
     *
     * @param string $className
     */
    public function test_mutators_do_not_declare_public_methods(string $className)
    {
        $rc = new \ReflectionClass($className);

        $this->assertCount(
            3,
            $this->getPublicMethods($rc),
            sprintf(
                'Mutator class "%s" has declared a public method, and should not do so, please consider refactoring.',
                $className
            )
        );
    }

    /**
     * @dataProvider provideConcreteMutatorClasses
     *
     * @param string $className
     */
    public function test_mutators_have_tests(string $className)
    {
        $testClassName = str_replace('Infection\\', 'Infection\Tests\\', $className) . 'Test';

        $this->assertTrue(
            class_exists($testClassName),
            sprintf(
                'Mutator "%s" does not have a corresponding unit test "%s", please fix this by adding tests.',
                $className,
                $testClassName
            )
        );
    }

    public function provideConcreteMutatorClasses()
    {
        return array_map(
            static function ($item) {
                return [$item];
            },
            $this->getConcreteMutatorClasses()
        );
    }

    public function providesMutatorClasses()
    {
        return array_map(
            static function ($item) {
                return [$item];
            },
            $this->getMutatorClasses()
        );
    }

    private function getMutatorClasses()
    {
        static $classes;

        if (null !== $classes) {
            return $classes;
        }

        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in(__DIR__ . '/../../src/Mutator')
            ->exclude([
                'Util',
            ])
        ;

        $classes = array_map(
            static function (SplFileInfo $file) {
                return sprintf(
                    '%s\\%s%s%s',
                    'Infection\\Mutator',
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

    private function getConcreteMutatorClasses()
    {
        return array_filter(
            $this->getMutatorClasses(),
            static function ($item) {
                $class = new \ReflectionClass($item);

                return !$class->isInterface() && !$class->isAbstract() && !$class->isTrait();
            }
        );
    }

    private function getPublicMethods(\ReflectionClass $rc)
    {
        $publicMethods = [];

        foreach ($rc->getMethods() as $method) {
            if ($method->isPublic() && !$method->isConstructor()) {
                $publicMethods[] = $method->name;
            }
        }
        sort($publicMethods);

        return $publicMethods;
    }
}
