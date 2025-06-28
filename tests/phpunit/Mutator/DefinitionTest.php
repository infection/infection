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

use function array_diff_key;
use function array_fill_keys;
use function array_flip;
use Infection\Mutator\Definition;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use Infection\Mutator\ProfileList;
use Infection\Testing\SingletonContainer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function sprintf;

#[CoversClass(Definition::class)]
final class DefinitionTest extends TestCase
{
    // TODO: address those
    private const MUTATORS_WITHOUT_REMEDIES = [
        'Assignment',
        'AssignmentEqual',
        'BitwiseAnd',
        'BitwiseNot',
        'BitwiseOr',
        'BitwiseXor',
        'Decrement',
        'DivEqual',
        'Division',
        'Exponentiation',
        'Increment',
        'Minus',
        'MinusEqual',
        'ModEqual',
        'Modulus',
        'MulEqual',
        'Multiplication',
        'Plus',
        'PlusEqual',
        'PowEqual',
        'RoundingFamily',
        'ShiftLeft',
        'ShiftRight',
        'ArrayItem',
        'FalseValue',
        'InstanceOf_',
        'LogicalAnd',
        'LogicalAndAllSubExprNegation',
        'LogicalAndNegation',
        'LogicalAndSingleSubExprNegation',
        'LogicalLowerAnd',
        'LogicalLowerOr',
        'LogicalNot',
        'LogicalOr',
        'LogicalOrAllSubExprNegation',
        'LogicalOrNegation',
        'LogicalOrSingleSubExprNegation',
        'NotEqualNotIdentical',
        'NotIdenticalNotEqual',
        'TrueValue',
        'Yield_',
        'GreaterThan',
        'GreaterThanOrEqualTo',
        'LessThan',
        'LessThanOrEqualTo',
        'Equal',
        'GreaterThanNegotiation',
        'GreaterThanOrEqualToNegotiation',
        'Identical',
        'LessThanNegotiation',
        'LessThanOrEqualToNegotiation',
        'NotEqual',
        'NotIdentical',
        'ProtectedVisibility',
        'PublicVisibility',
        'DecrementInteger',
        'IncrementInteger',
        'OneZeroFloat',
        'AssignCoalesce',
        'Break_',
        'Coalesce',
        'Concat',
        'Continue_',
        'ElseIfNegation',
        'Finally_',
        'IfNegation',
        'NullSafeMethodCall',
        'NullSafePropertyCall',
        'SpreadAssignment',
        'SpreadOneItem',
        'SpreadRemoval',
        'Ternary',
        'Throw_',
        'Catch_',
        'PregMatchMatches',
        'PregMatchRemoveCaret',
        'PregMatchRemoveDollar',
        'PregMatchRemoveFlags',
        'PregQuote',
        'ArrayItemRemoval',
        'CatchBlockRemoval',
        'CloneRemoval',
        'ConcatOperandRemoval',
        'FunctionCallRemoval',
        'MatchArmRemoval',
        'MethodCallRemoval',
        'SharedCaseRemoval',
        'ArrayOneItem',
        'FloatNegation',
        'FunctionCall',
        'IntegerNegation',
        'NewObject',
        'This',
        'YieldValue',
        'Spaceship',
        'DoWhile',
        'Foreach_',
        'For_',
        'While_',
        'CastArray',
        'CastBool',
        'CastFloat',
        'CastInt',
        'CastObject',
        'CastString',
        'UnwrapArrayChangeKeyCase',
        'UnwrapArrayChunk',
        'UnwrapArrayColumn',
        'UnwrapArrayCombine',
        'UnwrapArrayDiff',
        'UnwrapArrayDiffAssoc',
        'UnwrapArrayDiffKey',
        'UnwrapArrayDiffUassoc',
        'UnwrapArrayDiffUkey',
        'UnwrapArrayFilter',
        'UnwrapArrayFlip',
        'UnwrapArrayIntersect',
        'UnwrapArrayIntersectAssoc',
        'UnwrapArrayIntersectKey',
        'UnwrapArrayIntersectUassoc',
        'UnwrapArrayIntersectUkey',
        'UnwrapArrayKeys',
        'UnwrapArrayMerge',
        'UnwrapArrayMergeRecursive',
        'UnwrapArrayPad',
        'UnwrapArrayReduce',
        'UnwrapArrayReplace',
        'UnwrapArrayReplaceRecursive',
        'UnwrapArrayReverse',
        'UnwrapArraySlice',
        'UnwrapArraySplice',
        'UnwrapArrayUdiff',
        'UnwrapArrayUdiffAssoc',
        'UnwrapArrayUdiffUassoc',
        'UnwrapArrayUintersect',
        'UnwrapArrayUintersectAssoc',
        'UnwrapArrayUintersectUassoc',
        'UnwrapArrayUnique',
        'UnwrapArrayValues',
        'UnwrapLcFirst',
        'UnwrapLtrim',
        'UnwrapRtrim',
        'UnwrapStrIreplace',
        'UnwrapStrRepeat',
        'UnwrapStrReplace',
        'UnwrapStrRev',
        'UnwrapStrShuffle',
        'UnwrapStrToLower',
        'UnwrapStrToUpper',
        'UnwrapSubstr',
        'UnwrapTrim',
        'UnwrapUcFirst',
        'UnwrapUcWords',
        'UnwrapFinally',
        'BCMath',
        'MBString',
        'SyntaxError',
    ];

    #[DataProvider('valuesProvider')]
    public function test_it_can_be_instantiated(
        string $description,
        string $category,
        ?string $remedies,
        ?string $diff,
    ): void {
        $definition = new Definition($description, $category, $remedies, $diff);

        $this->assertSame($description, $definition->getDescription());
        $this->assertSame($category, $definition->getCategory());
        $this->assertSame($remedies, $definition->getRemedies());
        $this->assertSame($diff, $definition->getDiff());
    }

    public static function valuesProvider(): iterable
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

    #[DataProvider('mutatorsProvider')]
    public function test_it_must_define_remedies(Mutator $mutator): void
    {
        $this->assertNotNull(
            $mutator::getDefinition()->getRemedies(),
            sprintf(
                'Definition of [%s] must provide remedies.',
                $mutator->getName(),
            ),
        );
    }

    public static function mutatorsProvider(): iterable
    {
        $mutatorFactory = SingletonContainer::getContainer()->getMutatorFactory();

        $mutators = $mutatorFactory->create(
            array_fill_keys(
                ProfileList::ALL_MUTATORS,
                [],
            ),
            false,
        );

        $checkedMutators = array_diff_key(
            $mutators,
            array_flip(self::MUTATORS_WITHOUT_REMEDIES),
        );

        foreach ($checkedMutators as $name => $mutator) {
            self::assertInstanceOf(Mutator::class, $mutator);

            yield $name => [$mutator];
        }
    }
}
