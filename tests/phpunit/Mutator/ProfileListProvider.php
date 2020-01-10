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

use function array_filter;
use const ARRAY_FILTER_USE_KEY;
use function array_values;
use Generator;
use Infection\Mutator\Mutator;
use Infection\Mutator\ProfileList;
use function ksort;
use ReflectionClass;
use function Safe\realpath;
use const SORT_STRING;
use function sprintf;
use function str_replace;
use function substr;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\PathUtil\Path;

final class ProfileListProvider
{
    /**
     * @var array<int, array<int, string>>|null
     */
    private static $mutators;

    /**
     * @var array<string,string[]>|null
     */
    private static $profileConstants;

    private function __construct()
    {
    }

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

            return;
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

            $mutatorReflection = new ReflectionClass($className);

            if ($mutatorReflection->isAbstract()) {
                continue;
            }

            if (!$mutatorReflection->implementsInterface(Mutator::class)) {
                continue;
            }

            $mutators[$className] = [
                realpath($file->getPathname()),
                $className,
                $shortClassName,
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

    public static function profileProvider(): Generator
    {
        foreach (self::getProfiles() as $profile => $profileOrMutators) {
            yield $profile => [
                $profile,
                $profileOrMutators,
            ];
        }
    }

    private static function getMutatorClassNameFromPath(string $path): string
    {
        $cleanedRelativePath = substr(
            Path::makeRelative($path, __DIR__ . '/../../../src'),
            0,
            -4
        );

        return sprintf(
            'Infection\%s',
            str_replace('/', '\\', $cleanedRelativePath)
        );
    }
}
