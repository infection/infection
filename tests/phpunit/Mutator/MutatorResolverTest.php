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

use function array_diff;
use function array_values;
use function count;
use Infection\Mutator\Arithmetic\Minus;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Boolean\IdenticalEqual;
use Infection\Mutator\Boolean\NotIdenticalNotEqual;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\Extensions\MBString;
use Infection\Mutator\Loop\For_;
use Infection\Mutator\MutatorResolver;
use Infection\Mutator\Number\DecrementInteger;
use Infection\Mutator\Number\IncrementInteger;
use Infection\Mutator\Number\OneZeroFloat;
use Infection\Mutator\ProfileList;
use Infection\Tests\SingletonContainer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function sprintf;

final class MutatorResolverTest extends TestCase
{
    /**
     * @var MutatorResolver
     */
    private $mutatorResolver;

    protected function setUp(): void
    {
        $this->mutatorResolver = SingletonContainer::getContainer()->getMutatorResolver();
    }

    public function test_it_resolves_no_mutator_if_no_profile_or_mutator_is_passed(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([]);

        $this->assertCount(0, $resolvedMutators);
    }

    public function test_it_can_resolve_the_mutators_for_a_given_profile(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve(['@boolean' => true]);

        $this->assertSameMutatorsByClass(
            ProfileList::BOOLEAN_PROFILE,
            $resolvedMutators,
        );
    }

    public function test_it_can_resolve_the_mutators_with_empty_settings_for_a_given_profile(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve(['@boolean' => []]);

        $this->assertSameMutatorsByClass(
            ProfileList::BOOLEAN_PROFILE,
            $resolvedMutators,
        );
    }

    public function test_it_can_resolve_the_profile_mutators_with_the_given_settings(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            '@default' => true,
            '@boolean' => $settings = [
                'ignore' => ['A::B'],
            ],
        ]);

        $this->assertSameMutatorsByClass(
            ProfileList::getDefaultProfileMutators(),
            $resolvedMutators,
        );

        foreach (ProfileList::BOOLEAN_PROFILE as $booleanMutatorClassName) {
            $this->assertSame($settings, $resolvedMutators[$booleanMutatorClassName]);
        }
    }

    public function test_it_can_ignore_a_profile(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve(['@boolean' => false]);

        $this->assertCount(0, $resolvedMutators);
    }

    public function test_it_will_remove_the_mutators_from_the_ignored_profile_even_if_included_from_a_different_profile(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            '@default' => true,
            '@boolean' => false,
        ]);

        $expectedMutators = array_values(array_diff(
            ProfileList::getDefaultProfileMutators(),
            ProfileList::BOOLEAN_PROFILE,
        ));

        $this->assertSameMutatorsByClass($expectedMutators, $resolvedMutators);
    }

    public function test_it_will_not_remove_the_mutators_from_the_ignored_profile_if_its_mutators_are_included_after(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            '@default' => false,
            '@boolean' => true,
        ]);

        $this->assertSameMutatorsByClass(
            ProfileList::BOOLEAN_PROFILE,
            $resolvedMutators,
        );
    }

    public function test_it_can_resolve_mutators_from_their_names(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            MutatorName::getName(Plus::class) => true,
            MutatorName::getName(Minus::class) => true,
        ]);

        $this->assertSameMutatorsByClass(
            [
                Plus::class,
                Minus::class,
            ],
            $resolvedMutators,
        );
    }

    public function test_it_can_resolve_mutators_with_empty_settings_from_their_names(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            MutatorName::getName(Plus::class) => [],
            MutatorName::getName(Minus::class) => [],
        ]);

        $this->assertSameMutatorsByClass(
            [
                Plus::class,
                Minus::class,
            ],
            $resolvedMutators,
        );
    }

    public function test_it_can_resolve_a_mutator_with_the_given_settings(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            '@boolean' => true,
            MutatorName::getName(TrueValue::class) => $settings = [
                'ignore' => ['A::B'],
            ],
        ]);

        $this->assertSameMutatorsByClass(
            ProfileList::BOOLEAN_PROFILE,
            $resolvedMutators,
        );

        $this->assertSame($settings, $resolvedMutators[TrueValue::class]);
    }

    public function test_it_can_ignore_a_mutator(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve(
            [MutatorName::getName(Plus::class) => false],
        );

        $this->assertCount(0, $resolvedMutators);
    }

    public function test_it_will_remove_the_ignored_mutators_if_they_were_included_previously(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            '@equal' => true,
            MutatorName::getName(IdenticalEqual::class) => false,
        ]);

        $this->assertSameMutatorsByClass(
            [NotIdenticalNotEqual::class],
            $resolvedMutators,
        );
    }

    public function test_it_will_not_remove_the_ignored_mutators_if_they_were_included_afterwards(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            MutatorName::getName(IdenticalEqual::class) => false,
            '@equal' => true,
        ]);

        $this->assertSameMutatorsByClass(
            ProfileList::EQUAL_PROFILE,
            $resolvedMutators,
        );
    }

    public function test_a_mutator_will_be_resolved_only_once_even_if_included_multiple_times(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            MutatorName::getName(IdenticalEqual::class) => true,
            '@equal' => true,
            MutatorName::getName(NotIdenticalNotEqual::class) => true,
        ]);

        $this->assertSameMutatorsByClass(
            ProfileList::EQUAL_PROFILE,
            $resolvedMutators,
        );
    }

    public function test_it_can_resolve_mutators_with_global_settings(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            'global-ignore' => ['A::B'],
            MutatorName::getName(Plus::class) => true,
            MutatorName::getName(For_::class) => false,
            MutatorName::getName(IdenticalEqual::class) => [
                'ignore' => ['B::C'],
            ],
        ]);

        $this->assertSameMutatorsByClass(
            [
                Plus::class,
                IdenticalEqual::class,
            ],
            $resolvedMutators,
        );

        $this->assertSame(['ignore' => ['A::B']], $resolvedMutators[Plus::class]);
        $this->assertSame(['ignore' => ['A::B', 'B::C']], $resolvedMutators[IdenticalEqual::class]);
    }

    public function test_it_can_resolve_mutators_with_global_ignore_source_code_by_regex_setting(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            'global-ignoreSourceCodeByRegex' => ['A::B'],
            MutatorName::getName(Plus::class) => true,
            MutatorName::getName(For_::class) => false,
            MutatorName::getName(IdenticalEqual::class) => [
                'ignoreSourceCodeByRegex' => ['B::C'],
            ],
        ]);

        $this->assertSameMutatorsByClass(
            [
                Plus::class,
                IdenticalEqual::class,
            ],
            $resolvedMutators,
        );

        $this->assertSame(['ignoreSourceCodeByRegex' => ['A::B']], $resolvedMutators[Plus::class]);
        $this->assertSame(['ignoreSourceCodeByRegex' => ['A::B', 'B::C']], $resolvedMutators[IdenticalEqual::class]);
    }

    public function test_it_can_resolve_mutators_with_both_global_settings_at_the_same_time(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            'global-ignore' => ['A::B'],
            'global-ignoreSourceCodeByRegex' => ['A::B'],
            MutatorName::getName(Plus::class) => true,
            MutatorName::getName(For_::class) => false,
            MutatorName::getName(IdenticalEqual::class) => [
                'ignore' => ['B::C'],
                'ignoreSourceCodeByRegex' => ['B::C'],
            ],
        ]);

        $this->assertSameMutatorsByClass(
            [
                Plus::class,
                IdenticalEqual::class,
            ],
            $resolvedMutators,
        );

        $this->assertSame(
            [
                'ignore' => ['A::B'],
                'ignoreSourceCodeByRegex' => ['A::B'],
            ],
            $resolvedMutators[Plus::class],
        );
        $this->assertSame(
            [
                'ignore' => ['A::B', 'B::C'],
                'ignoreSourceCodeByRegex' => ['A::B', 'B::C'],
            ],
            $resolvedMutators[IdenticalEqual::class],
        );
    }

    public function test_it_can_resolve_mutators_with_duplicate_global_settings(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            'global-ignoreSourceCodeByRegex' => ['A::B', 'A::B', 'C::D'],
            MutatorName::getName(Plus::class) => true,
            MutatorName::getName(For_::class) => false,
            MutatorName::getName(MBString::class) => [
                'settings' => ['mb_substr' => false],
            ],
            MutatorName::getName(IdenticalEqual::class) => [
                'ignoreSourceCodeByRegex' => [],
            ],
        ]);

        $this->assertSameMutatorsByClass(
            [
                Plus::class,
                MBString::class,
                IdenticalEqual::class,
            ],
            $resolvedMutators,
        );

        $this->assertSame(
            [
                'ignoreSourceCodeByRegex' => ['A::B', 'C::D'],
            ],
            $resolvedMutators[Plus::class],
        );

        $this->assertSame(
            [
                'ignoreSourceCodeByRegex' => ['A::B', 'C::D'],
                'settings' => ['mb_substr' => false],
            ],
            $resolvedMutators[MBString::class],
        );

        $this->assertSame(
            [
                'ignoreSourceCodeByRegex' => ['A::B', 'C::D'],
            ],
            $resolvedMutators[IdenticalEqual::class],
        );
    }

    public function test_it_can_resolve_mutators_with_duplicate_global_and_per_mutator_settings(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            'global-ignore' => ['A::B'],
            'global-ignoreSourceCodeByRegex' => ['A::B', 'A::B', 'C::D'],
            MutatorName::getName(Plus::class) => true,
            MutatorName::getName(For_::class) => false,
            MutatorName::getName(MBString::class) => [
                'settings' => ['mb_substr' => false],
            ],
            MutatorName::getName(IdenticalEqual::class) => [
                'ignore' => ['B::C'],
                'ignoreSourceCodeByRegex' => ['A::B', 'B::C'],
            ],
        ]);

        $this->assertSameMutatorsByClass(
            [
                Plus::class,
                MBString::class,
                IdenticalEqual::class,
            ],
            $resolvedMutators,
        );

        $this->assertSame(
            [
                'ignore' => ['A::B'],
                'ignoreSourceCodeByRegex' => ['A::B', 'C::D'],
            ],
            $resolvedMutators[Plus::class],
        );
        $this->assertSame(
            [
                'ignore' => ['A::B'],
                'ignoreSourceCodeByRegex' => ['A::B', 'C::D'],
                'settings' => ['mb_substr' => false],
            ],
            $resolvedMutators[MBString::class],
        );
        $this->assertSame(
            [
                'ignore' => ['A::B', 'B::C'],
                'ignoreSourceCodeByRegex' => ['A::B', 'C::D', 'B::C'],
            ],
            $resolvedMutators[IdenticalEqual::class],
        );
    }

    public function test_it_always_enrich_global_settings_for_a_mutator_regardless_of_the_order(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            MutatorName::getName(Plus::class) => [
                'ignore' => ['B::C'],
            ],
            'global-ignore' => ['A::B'],
        ]);

        $this->assertSameMutatorsByClass([Plus::class], $resolvedMutators);

        $this->assertSame(['ignore' => ['A::B', 'B::C']], $resolvedMutators[Plus::class]);
    }

    public function test_it_cannot_resolve_mutators_for_unknown_profiles(): void
    {
        try {
            $this->mutatorResolver->resolve(['@unknown-profile' => true]);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'The profile or mutator "@unknown-profile" was not recognized.',
                $exception->getMessage(),
            );
        }
    }

    public function test_it_cannot_resolve_an_unknown_mutator(): void
    {
        try {
            $this->mutatorResolver->resolve(['Unknwon\Mutator' => true]);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'The profile or mutator "Unknwon\Mutator" was not recognized.',
                $exception->getMessage(),
            );
        }
    }

    public function test_it_correct_when_profile_overrides_mutator(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            'DecrementInteger' => [
                'ignore' => [
                    'Infected\\SourceClass::add',
                ],
            ],
            '@number' => true,
        ]);

        $this->assertSame(
            [
                DecrementInteger::class => [
                    'ignore' => [
                        'Infected\\SourceClass::add',
                    ],
                ],
                IncrementInteger::class => [
                ],
                OneZeroFloat::class => [
                ],
            ],
            $resolvedMutators,
        );
    }

    public function test_it_correct_when_mutator_overrides_profile(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            '@number' => true,
            'DecrementInteger' => [
                'ignore' => [
                    'Infected\\SourceClass::add',
                ],
            ],
        ]);

        $this->assertSame(
            [
                DecrementInteger::class => [
                    'ignore' => [
                        'Infected\\SourceClass::add',
                    ],
                ],
                IncrementInteger::class => [
                ],
                OneZeroFloat::class => [
                ],
            ],
            $resolvedMutators,
        );
    }

    public function test_it_correct_when_mutator_overrides_profile_with_settings(): void
    {
        $resolvedMutators = $this->mutatorResolver->resolve([
            '@number' => [
                'ignore' => [
                    'Infected\\SourceClass::substract',
                ],
            ],
            'DecrementInteger' => [
                'ignore' => [
                    'Infected\\SourceClass::add',
                ],
            ],
        ]);

        $this->assertSame(
            [
                DecrementInteger::class => [
                    'ignore' => [
                        'Infected\\SourceClass::add',
                        'Infected\\SourceClass::substract',
                    ],
                ],
                IncrementInteger::class => [
                    'ignore' => [
                        'Infected\\SourceClass::substract',
                    ],
                ],
                OneZeroFloat::class => [
                    'ignore' => [
                        'Infected\\SourceClass::substract',
                    ],
                ],
            ],
            $resolvedMutators,
        );
    }

    /**
     * @param string[] $expectedMutators
     * @param array<string, mixed[]> $actualMutators
     */
    private function assertSameMutatorsByClass(array $expectedMutators, array $actualMutators): void
    {
        $this->assertCount(count($expectedMutators), $actualMutators);

        $index = 0;

        foreach ($actualMutators as $mutatorClassName => $settings) {
            $this->assertContains($mutatorClassName, ProfileList::ALL_MUTATORS);

            $expectedMutatorClass = $expectedMutators[$index];

            $this->assertSame(
                $expectedMutatorClass,
                $mutatorClassName,
                sprintf(
                    'Expected the %d-th mutator to be an instance of "%s". Got "%s"',
                    $index,
                    $expectedMutatorClass,
                    $mutatorClassName,
                ),
            );

            $this->assertIsArray($settings);

            ++$index;
        }
    }
}
