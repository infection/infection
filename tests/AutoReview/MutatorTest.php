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

namespace Infection\Tests\AutoReview;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 *
 * @coversNothing
 *
 * @group infra
 *
 * This class is responsible for testing that all Mutator classes adhere to certain rules
 * e.g. 'Mutators shouldn't declare any public methods`
 */
final class MutatorTest extends TestCase
{
    /**
     * @dataProvider providesMutatorClasses
     */
    public function test_mutator_class_provider_is_valid(string $className): void
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
     */
    public function test_mutators_do_not_declare_public_methods(string $className): void
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
     */
    public function test_mutators_have_tests(string $className): void
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
