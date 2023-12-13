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

use function array_fill_keys;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\Boolean\TrueValueConfig;
use Infection\Mutator\Definition;
use Infection\Mutator\Extensions\BCMath;
use Infection\Mutator\Extensions\BCMathConfig;
use Infection\Mutator\Extensions\MBString;
use Infection\Mutator\Extensions\MBStringConfig;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use Infection\Mutator\ProfileList;
use Infection\Mutator\Removal\ArrayItemRemoval;
use Infection\Mutator\Removal\ArrayItemRemovalConfig;
use Infection\Tests\SingletonContainer;
use PHPUnit\Framework\TestCase;
use function sprintf;

final class DefinitionTest extends TestCase
{
    /**
     * @dataProvider valuesProvider
     */
    public function test_it_can_be_instantiated(
        string $description,
        string $category,
        ?string $remedies,
        ?string $diff
    ): void {
        $definition = new Definition($description, $category, $remedies, $diff);

        $this->assertSame($description, $definition->getDescription());
        $this->assertSame($category, $definition->getCategory());
        $this->assertSame($remedies, $definition->getRemedies());
        $this->assertSame($diff, $definition->getDiff());
    }

    public function valuesProvider(): iterable
    {
        yield 'empty' => [
            '',
            MutatorCategory::SEMANTIC_REDUCTION,
            null,
            '',
        ];

        yield 'nominal' => [
            'This text is for explaining what the mutator is about.',
            MutatorCategory::SEMANTIC_REDUCTION,
            'This text is for providing guidelines on how to kill the mutant.',
            'The diff',
        ];
    }

    /**
     * @dataProvider mutatorsProvider
     */
    public function test_it_must_be_instantiated_with_remedies(
        Mutator $mutator
    ): void {
        $this->assertNotNull(
            $mutator->getDefinition()->getRemedies(),
            sprintf(
                'Definition of [%s] must provide remedies.',
                $mutator->getName(),
            ),
        );
    }

    public function mutatorsProvider(): iterable
    {
        $mutatorFactory = SingletonContainer::getContainer()->getMutatorFactory();

        $mutators = $mutatorFactory->create(array_fill_keys(
            ProfileList::ALL_MUTATORS,
            []
        ), false);

        foreach ($mutators as $name => $mutator) {
            $this->assertInstanceOf(Mutator::class, $mutator);

            switch ($mutator) {
                case $mutator instanceof TrueValue:
                    $actualMutatorClass = new TrueValue(new TrueValueConfig([]));

                    break;
                case $mutator instanceof ArrayItemRemoval:
                    $actualMutatorClass = new ArrayItemRemoval(new ArrayItemRemovalConfig([]));

                    break;
                case $mutator instanceof BCMath:
                    $actualMutatorClass = new BCMath(new BCMathConfig([]));

                    break;
                case $mutator instanceof MBString:
                    $actualMutatorClass = new MBString(new MBStringConfig([]));

                    break;
                default:
                    $actualMutatorClass = new $mutator();

                    break;
            }

            yield $name => [
                $actualMutatorClass,
            ];
        }
    }
}
