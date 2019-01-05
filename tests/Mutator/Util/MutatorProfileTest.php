<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
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

namespace Infection\Tests\Mutator\Util;

use Infection\Mutator\Util\MutatorProfile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
final class MutatorProfileTest extends TestCase
{
    public function test_all_mutators_have_the_correct_name_in_the_full_mutator_list(): void
    {
        foreach (MutatorProfile::FULL_MUTATOR_LIST as $name => $class) {
            $this->assertSame(
                $name,
                $class::getName(),
                sprintf(
                    'Invalid name "%s" provided for the class "%s", expected "%s" as key',
                    $name,
                    $class,
                    $class::getName()
                )
            );
        }
    }

    public function test_all_mutators_are_part_of_the_full_mutators_list(): void
    {
        foreach ($this->getMutatorFiles() as $file) {
            $class = substr($file->getFilename(), 0, -4);

            $this->assertArrayHasKey(
                $class,
                MutatorProfile::FULL_MUTATOR_LIST,
                sprintf(
                    'The mutator "%s" located in "%s" has not been added to the FULL_MUTATOR_LIST in the MutatorProfile class. ' .
                    'Please add it to ensure it can be used.',
                    $class,
                    $file->getPath()
                )
            );
        }
    }

    public function test_all_mutators_are_part_of_at_least_one_profile(): void
    {
        $profileConstants = $this->getMutatorProfileConstants();

        foreach ($this->getMutatorFiles() as $file) {
            $className = substr($file->getFilename(), 0, -4);
            $relativeClassName = str_replace(
                '/',
                '\\',
                substr($file->getRelativePathname(), 0, -4)
            );

            $this->assertTrue(
                $this->isMutatorInAtLeastOneProfile($relativeClassName, $profileConstants),
                sprintf(
                    'The mutator "%s" located in "%s" has not been added to any profile in the MutatorProfile class. ' .
                    'Please add it to ensure it can be used.',
                    $className,
                    $file->getPath()
                )
            );
        }
    }

    /**
     * @dataProvider providerMutatorProfile
     */
    public function test_all_mutator_profiles_are_sorted(string $name, array $mutators): void
    {
        $sorted = $mutators;

        sort($sorted);

        $this->assertSame($sorted, $mutators, sprintf(
            'Failed asserting that mutators listed in profile "%s" are sorted by name, please sort them.',
            $name
        ));
    }

    public function providerMutatorProfile(): \Generator
    {
        foreach ($this->getMutatorProfileConstants() as $name => $mutators) {
            yield $name => [
                $name,
                $mutators,
            ];
        }
    }

    private function getMutatorFiles(): Finder
    {
        return Finder::create()
            ->name('*.php')
            ->in('src/Mutator')
            ->exclude('Util')
            ->notName('/Abstract.*/')
            ->files();
    }

    private function getMutatorProfileConstants(): array
    {
        $reflectionClass = new \ReflectionClass(MutatorProfile::class);
        $excludedConstants = ['MUTATOR_PROFILE_LIST', 'DEFAULT', 'FULL_MUTATOR_LIST'];

        return array_filter(
            $reflectionClass->getConstants(),
            function (string $constantName) use ($excludedConstants): bool {
                return !\in_array($constantName, $excludedConstants, true);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    private function isMutatorInAtLeastOneProfile(string $relativeClassName, array $profiles): bool
    {
        foreach ($profiles as $mutatorsInProfile) {
            $fqcn = sprintf('Infection\\Mutator\\%s', $relativeClassName);

            if (\in_array($fqcn, $mutatorsInProfile, true)) {
                return true;
            }
        }

        return false;
    }
}
