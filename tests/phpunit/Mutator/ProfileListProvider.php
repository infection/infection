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

namespace Infection\Tests\Mutator;

use Generator;
use Infection\Mutator\ProfileList;
use Infection\Mutator\Util\Mutator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\PathUtil\Path;
use function array_filter;
use function array_map;
use function array_values;
use function in_array;
use function iterator_to_array;
use function ksort;
use function sort;
use function sprintf;
use function str_replace;
use function substr;
use const ARRAY_FILTER_USE_KEY;
use const DIRECTORY_SEPARATOR;
use const SORT_STRING;
use function Safe\realpath;

final class ProfileListProvider
{
    /**
     * @var string[]|null
     */
    private static $mutators;

    /**
     * @var array<string,string[]>|null
     */
    private static $profileConstants;

    public static function mutatorNameAndClassProvider(): Generator
    {
        foreach (ProfileList::ALL_MUTATORS as $name => $class) {
            yield [$name, $class];
        }
    }

    public static function implementedMutatorProvider(): Generator
    {
        if (null !== self::$mutators) {
            yield from self::$mutators;
        }

        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in(__DIR__ . '/../../../src/Mutator')
            ->exclude('Util')
        ;

        $mutators = [];

        foreach ($finder as $file) {
            /** @var SplFileInfo $file */

            $shortClassName = substr($file->getFilename(), 0, -4);
            $className = self::getMutatorClassNameFromPath($file->getPathname());

            $relativeClassName = str_replace(
                '/',
                '\\',
                substr($file->getRelativePathname(), 0, -4)
            );

            $mutatorReflection = new ReflectionClass($className);

            if ($mutatorReflection->isAbstract()) {
                continue;
            }

            $mutatorParentReflection = $mutatorReflection->getParentClass();

            if (false === $mutatorParentReflection || Mutator::class !== $mutatorParentReflection->getName()) {
                continue;
            }

            $mutators[$className] = [
                realpath($file->getPath()),
                $className,
                $shortClassName,
                $relativeClassName,
            ];
        }

        ksort($mutators, SORT_STRING);

        self::$mutators = array_values($mutators);

        yield from self::$mutators;
    }

    public static function getProfiles(): array
    {
        if (null !== self::$profileConstants) {
            return self::$profileConstants;
        }

        $profileListReflection = new ReflectionClass(ProfileList::class);

        self::$profileConstants = array_filter(
            $profileListReflection->getConstants(),
            static function (string $constantName): bool {
                return substr($constantName, -8) === '_PROFILE';
            },
            ARRAY_FILTER_USE_KEY
        );

        return self::$profileConstants;
    }

    private static function getMutatorClassNameFromPath(string $path): string
    {
        $cleanedRelativePath = substr(
            Path::makeRelative($path, __DIR__.'/../../../src'),
            0,
            -4
        );

        return sprintf(
            'Infection\%s',
            str_replace('/', '\\', $cleanedRelativePath)
        );
    }

    public static function profileProvider(): Generator
    {
        foreach (self::getProfiles() as $profile => $profileOrMutators) {
            yield $profile => [
                $profile,
                $profileOrMutators,
            ];
        }
    }

    private function __construct()
    {
    }
}
