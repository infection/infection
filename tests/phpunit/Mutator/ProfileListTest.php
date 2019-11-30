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
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use function array_keys;
use function in_array;
use function sort;
use function sprintf;
use function str_replace;
use function substr;
use const SORT_STRING;

final class ProfileListTest extends TestCase
{
    /**
     * @dataProvider \Infection\Tests\Mutator\ProfileListProvider::mutatorNameAndClassProvider
     */
    public function test_all_mutators_to_be_listed_by_their_short_and_fully_qualified_class_names(
        string $expectedMutatorName,
        string $mutatorClass
    ): void
    {
        $actualMutatorName = $mutatorClass::getName();

        $this->assertSame(
            $expectedMutatorName,
            $actualMutatorName,
            sprintf(
                'Expected the name "%s" for the mutator "%s". Got "%s"',
                $actualMutatorName,
                $expectedMutatorName,
                $mutatorClass
            )
        );
    }

    /**
     * @dataProvider \Infection\Tests\Mutator\ProfileListProvider::implementedMutatorProvider
     */
    public function test_all_mutators_are_listed_in_the_all_mutators_constant(
        string $mutatorFilePath,
        string $mutatorClassName,
        string $mutatorShortClassName
    ): void
    {
        $this->assertArrayHasKey(
            $mutatorShortClassName,
            ProfileList::ALL_MUTATORS,
            sprintf(
                'Expected to find the mutator "%s" (found in "%s") to be listed in '
                .'%s::ALL_MUTATORS',
                $mutatorClassName,
                $mutatorFilePath,
                ProfileList::class
            )
        );
    }

    /**
     * @dataProvider \Infection\Tests\Mutator\ProfileListProvider::implementedMutatorProvider
     */
    public function test_all_mutators_are_listed_by_at_least_one_profile(
        string $mutatorFilePath,
        string $mutatorClassName
    ): void
    {
        $this->assertTrue(
            self::isMutatorInAtLeastOneProfile($mutatorClassName),
            sprintf(
                'Expected the mutator "%s" (found in "%s") to be listed in at least one '
                .'profile. Please add it to the appropriate %s::*_PROFILE constant',
                $mutatorClassName,
                $mutatorFilePath,
                ProfileList::class
            )
        );
    }

    /**
     * @dataProvider \Infection\Tests\Mutator\ProfileListProvider::profileProvider
     */
    public function test_all_mutator_profiles_are_sorted_lexicographically(
        string $profile,
        array $profileOrMutators
    ): void
    {
        $sortedProfileOrMutators = (static function (array $value): array {
            sort($value, SORT_STRING);

            return $value;
        })($profileOrMutators);

        $this->assertSame(
            $sortedProfileOrMutators,
            $profileOrMutators,
            sprintf(
                'Expected the profiles and mutators listed in %s::%s to be sorted lexicographically',
                ProfileList::class,
                $profile
            )
        );
    }

    public function test_the_all_profile_constant_lists_profiles_in_a_lexicographical_order(): void
    {
        $allProfiles = array_keys(ProfileList::ALL_PROFILES);

        $sortedAllProfiles = (static function (array $value): array {
            sort($value, SORT_STRING);

            return $value;
        })($allProfiles);

        $this->assertSame(
            $sortedAllProfiles,
            $allProfiles,
            sprintf(
                'Expected profiles in %s::ALL_PROFILES to be sorted lexicographically',
                ProfileList::class
            )
        );
    }

    private static function isMutatorInAtLeastOneProfile(string $className): bool
    {
        foreach (ProfileListProvider::getProfiles() as $profile => $profileOrMutators) {
            if (in_array($className, $profileOrMutators, true)) {
                return true;
            }
        }

        return false;
    }
}
