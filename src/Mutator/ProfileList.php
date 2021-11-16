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

namespace Infection\Mutator;

use function array_values;
use Infection\CannotBeInstantiated;
use Infection\Mutator;

/**
 * @internal
 */
final class ProfileList
{
    use CannotBeInstantiated;

    public const ALL_PROFILES = [
        '@arithmetic' => self::ARITHMETIC_PROFILE,
        '@boolean' => self::BOOLEAN_PROFILE,
        '@cast' => self::CAST_PROFILE,
        '@conditional_boundary' => self::CONDITIONAL_BOUNDARY_PROFILE,
        '@conditional_negotiation' => self::CONDITIONAL_NEGOTIATION_PROFILE,
        '@default' => self::DEFAULT_PROFILE,
        '@equal' => self::EQUAL_PROFILE,
        '@extensions' => self::EXTENSIONS_PROFILE,
        '@function_signature' => self::FUNCTION_SIGNATURE_PROFILE,
        '@identical' => self::IDENTICAL_PROFILE,
        '@loop' => self::LOOP_PROFILE,
        '@number' => self::NUMBER_PROFILE,
        '@operator' => self::OPERATOR_PROFILE,
        '@regex' => self::REGEX_PROFILE,
        '@removal' => self::REMOVAL_PROFILE,
        '@return_value' => self::RETURN_VALUE_PROFILE,
        '@sort' => self::SORT_PROFILE,
        '@unwrap' => self::UNWRAP_PROFILE,
    ];

    public const ARITHMETIC_PROFILE = [
        Mutator\Arithmetic\Assignment::class,
        Mutator\Arithmetic\AssignmentEqual::class,
        Mutator\Arithmetic\BitwiseAnd::class,
        Mutator\Arithmetic\BitwiseNot::class,
        Mutator\Arithmetic\BitwiseOr::class,
        Mutator\Arithmetic\BitwiseXor::class,
        Mutator\Arithmetic\Decrement::class,
        Mutator\Arithmetic\DivEqual::class,
        Mutator\Arithmetic\Division::class,
        Mutator\Arithmetic\Exponentiation::class,
        Mutator\Arithmetic\Increment::class,
        Mutator\Arithmetic\Minus::class,
        Mutator\Arithmetic\MinusEqual::class,
        Mutator\Arithmetic\ModEqual::class,
        Mutator\Arithmetic\Modulus::class,
        Mutator\Arithmetic\MulEqual::class,
        Mutator\Arithmetic\Multiplication::class,
        Mutator\Arithmetic\Plus::class,
        Mutator\Arithmetic\PlusEqual::class,
        Mutator\Arithmetic\PowEqual::class,
        Mutator\Arithmetic\RoundingFamily::class,
        Mutator\Arithmetic\ShiftLeft::class,
        Mutator\Arithmetic\ShiftRight::class,
    ];

    public const BOOLEAN_PROFILE = [
        Mutator\Boolean\ArrayItem::class,
        // EqualIdentical disabled from the default boolean profile
        Mutator\Boolean\FalseValue::class,
        // IdenticalEqual disabled from the default boolean profile
        Mutator\Boolean\InstanceOf_::class,
        Mutator\Boolean\LogicalAnd::class,
        Mutator\Boolean\LogicalLowerAnd::class,
        Mutator\Boolean\LogicalLowerOr::class,
        Mutator\Boolean\LogicalNot::class,
        Mutator\Boolean\LogicalOr::class,
        // NotEqualNotIdentical disabled from the default boolean profile
        // NotIdenticalNotEqual disabled from the default boolean profile
        Mutator\Boolean\TrueValue::class,
        Mutator\Boolean\Yield_::class,
    ];

    public const CONDITIONAL_BOUNDARY_PROFILE = [
        Mutator\ConditionalBoundary\GreaterThan::class,
        Mutator\ConditionalBoundary\GreaterThanOrEqualTo::class,
        Mutator\ConditionalBoundary\LessThan::class,
        Mutator\ConditionalBoundary\LessThanOrEqualTo::class,
    ];

    public const CONDITIONAL_NEGOTIATION_PROFILE = [
        Mutator\ConditionalNegotiation\Equal::class,
        Mutator\ConditionalNegotiation\GreaterThanNegotiation::class,
        Mutator\ConditionalNegotiation\GreaterThanOrEqualToNegotiation::class,
        Mutator\ConditionalNegotiation\Identical::class,
        Mutator\ConditionalNegotiation\LessThanNegotiation::class,
        Mutator\ConditionalNegotiation\LessThanOrEqualToNegotiation::class,
        Mutator\ConditionalNegotiation\NotEqual::class,
        Mutator\ConditionalNegotiation\NotIdentical::class,
    ];

    public const EQUAL_PROFILE = [
        Mutator\Boolean\IdenticalEqual::class,
        Mutator\Boolean\NotIdenticalNotEqual::class,
    ];

    public const FUNCTION_SIGNATURE_PROFILE = [
        Mutator\FunctionSignature\ProtectedVisibility::class,
        Mutator\FunctionSignature\PublicVisibility::class,
    ];

    public const IDENTICAL_PROFILE = [
        Mutator\Boolean\EqualIdentical::class,
        Mutator\Boolean\NotEqualNotIdentical::class,
    ];

    public const NUMBER_PROFILE = [
        Mutator\Number\DecrementInteger::class,
        Mutator\Number\IncrementInteger::class,
        Mutator\Number\OneZeroFloat::class,
    ];

    public const OPERATOR_PROFILE = [
        Mutator\Operator\AssignCoalesce::class,
        Mutator\Operator\Break_::class,
        Mutator\Operator\Coalesce::class,
        Mutator\Operator\Concat::class,
        Mutator\Operator\Continue_::class,
        Mutator\Operator\Finally_::class,
        Mutator\Operator\NullSafeMethodCall::class,
        Mutator\Operator\NullSafePropertyCall::class,
        Mutator\Operator\SpreadAssignment::class,
        Mutator\Operator\SpreadOneItem::class,
        Mutator\Operator\SpreadRemoval::class,
        Mutator\Operator\Ternary::class,
        Mutator\Operator\Throw_::class,
    ];

    public const REGEX_PROFILE = [
        Mutator\Regex\PregMatchMatches::class,
        Mutator\Regex\PregMatchRemoveCaret::class,
        Mutator\Regex\PregMatchRemoveDollar::class,
        Mutator\Regex\PregMatchRemoveFlags::class,
        Mutator\Regex\PregQuote::class,
    ];

    public const REMOVAL_PROFILE = [
        Mutator\Removal\ArrayItemRemoval::class,
        Mutator\Removal\CloneRemoval::class,
        Mutator\Removal\ConcatOperandRemoval::class,
        Mutator\Removal\FunctionCallRemoval::class,
        Mutator\Removal\MethodCallRemoval::class,
        Mutator\Removal\SharedCaseRemoval::class,
    ];

    public const RETURN_VALUE_PROFILE = [
        Mutator\ReturnValue\ArrayOneItem::class,
        Mutator\ReturnValue\FloatNegation::class,
        Mutator\ReturnValue\FunctionCall::class,
        Mutator\ReturnValue\IntegerNegation::class,
        Mutator\ReturnValue\NewObject::class,
        Mutator\ReturnValue\This::class,
        Mutator\ReturnValue\YieldValue::class,
    ];

    public const SORT_PROFILE = [
        Mutator\Sort\Spaceship::class,
    ];

    public const LOOP_PROFILE = [
        Mutator\Loop\DoWhile::class,
        Mutator\Loop\For_::class,
        Mutator\Loop\Foreach_::class,
        Mutator\Loop\While_::class,
    ];

    public const CAST_PROFILE = [
        Mutator\Cast\CastArray::class,
        Mutator\Cast\CastBool::class,
        Mutator\Cast\CastFloat::class,
        Mutator\Cast\CastInt::class,
        Mutator\Cast\CastObject::class,
        Mutator\Cast\CastString::class,
    ];

    public const UNWRAP_PROFILE = [
        Mutator\Unwrap\UnwrapArrayChangeKeyCase::class,
        Mutator\Unwrap\UnwrapArrayChunk::class,
        Mutator\Unwrap\UnwrapArrayColumn::class,
        Mutator\Unwrap\UnwrapArrayCombine::class,
        Mutator\Unwrap\UnwrapArrayDiff::class,
        Mutator\Unwrap\UnwrapArrayDiffAssoc::class,
        Mutator\Unwrap\UnwrapArrayDiffKey::class,
        Mutator\Unwrap\UnwrapArrayDiffUassoc::class,
        Mutator\Unwrap\UnwrapArrayDiffUkey::class,
        Mutator\Unwrap\UnwrapArrayFilter::class,
        Mutator\Unwrap\UnwrapArrayFlip::class,
        Mutator\Unwrap\UnwrapArrayIntersect::class,
        Mutator\Unwrap\UnwrapArrayIntersectAssoc::class,
        Mutator\Unwrap\UnwrapArrayIntersectKey::class,
        Mutator\Unwrap\UnwrapArrayIntersectUassoc::class,
        Mutator\Unwrap\UnwrapArrayIntersectUkey::class,
        Mutator\Unwrap\UnwrapArrayKeys::class,
        Mutator\Unwrap\UnwrapArrayMap::class,
        Mutator\Unwrap\UnwrapArrayMerge::class,
        Mutator\Unwrap\UnwrapArrayMergeRecursive::class,
        Mutator\Unwrap\UnwrapArrayPad::class,
        Mutator\Unwrap\UnwrapArrayReduce::class,
        Mutator\Unwrap\UnwrapArrayReplace::class,
        Mutator\Unwrap\UnwrapArrayReplaceRecursive::class,
        Mutator\Unwrap\UnwrapArrayReverse::class,
        Mutator\Unwrap\UnwrapArraySlice::class,
        Mutator\Unwrap\UnwrapArraySplice::class,
        Mutator\Unwrap\UnwrapArrayUdiff::class,
        Mutator\Unwrap\UnwrapArrayUdiffAssoc::class,
        Mutator\Unwrap\UnwrapArrayUdiffUassoc::class,
        Mutator\Unwrap\UnwrapArrayUintersect::class,
        Mutator\Unwrap\UnwrapArrayUintersectAssoc::class,
        Mutator\Unwrap\UnwrapArrayUintersectUassoc::class,
        Mutator\Unwrap\UnwrapArrayUnique::class,
        Mutator\Unwrap\UnwrapArrayValues::class,
        Mutator\Unwrap\UnwrapLcFirst::class,
        Mutator\Unwrap\UnwrapLtrim::class,
        Mutator\Unwrap\UnwrapRtrim::class,
        Mutator\Unwrap\UnwrapStrIreplace::class,
        Mutator\Unwrap\UnwrapStrRepeat::class,
        Mutator\Unwrap\UnwrapStrReplace::class,
        Mutator\Unwrap\UnwrapStrRev::class,
        Mutator\Unwrap\UnwrapStrShuffle::class,
        Mutator\Unwrap\UnwrapStrToLower::class,
        Mutator\Unwrap\UnwrapStrToUpper::class,
        Mutator\Unwrap\UnwrapSubstr::class,
        Mutator\Unwrap\UnwrapTrim::class,
        Mutator\Unwrap\UnwrapUcFirst::class,
        Mutator\Unwrap\UnwrapUcWords::class,
    ];

    public const EXTENSIONS_PROFILE = [
        Mutator\Extensions\BCMath::class,
        Mutator\Extensions\MBString::class,
    ];

    public const DEFAULT_PROFILE = [
        '@arithmetic',
        '@boolean',
        '@cast',
        '@conditional_boundary',
        '@conditional_negotiation',
        '@extensions',
        '@function_signature',
        '@loop',
        '@number',
        '@operator',
        '@regex',
        '@removal',
        '@return_value',
        '@sort',
        '@unwrap',
    ];

    public const ALL_MUTATORS = [
        // Arithmetic
        'Assignment' => Mutator\Arithmetic\Assignment::class,
        'AssignmentEqual' => Mutator\Arithmetic\AssignmentEqual::class,
        'BitwiseAnd' => Mutator\Arithmetic\BitwiseAnd::class,
        'BitwiseNot' => Mutator\Arithmetic\BitwiseNot::class,
        'BitwiseOr' => Mutator\Arithmetic\BitwiseOr::class,
        'BitwiseXor' => Mutator\Arithmetic\BitwiseXor::class,
        'Decrement' => Mutator\Arithmetic\Decrement::class,
        'DivEqual' => Mutator\Arithmetic\DivEqual::class,
        'Division' => Mutator\Arithmetic\Division::class,
        'Exponentiation' => Mutator\Arithmetic\Exponentiation::class,
        'Increment' => Mutator\Arithmetic\Increment::class,
        'Minus' => Mutator\Arithmetic\Minus::class,
        'MinusEqual' => Mutator\Arithmetic\MinusEqual::class,
        'ModEqual' => Mutator\Arithmetic\ModEqual::class,
        'Modulus' => Mutator\Arithmetic\Modulus::class,
        'MulEqual' => Mutator\Arithmetic\MulEqual::class,
        'Multiplication' => Mutator\Arithmetic\Multiplication::class,
        'Plus' => Mutator\Arithmetic\Plus::class,
        'PlusEqual' => Mutator\Arithmetic\PlusEqual::class,
        'PowEqual' => Mutator\Arithmetic\PowEqual::class,
        'RoundingFamily' => Mutator\Arithmetic\RoundingFamily::class,
        'ShiftLeft' => Mutator\Arithmetic\ShiftLeft::class,
        'ShiftRight' => Mutator\Arithmetic\ShiftRight::class,

        // Boolean
        'ArrayItem' => Mutator\Boolean\ArrayItem::class,
        'EqualIdentical' => Mutator\Boolean\EqualIdentical::class,
        'FalseValue' => Mutator\Boolean\FalseValue::class,
        'IdenticalEqual' => Mutator\Boolean\IdenticalEqual::class,
        'InstanceOf_' => Mutator\Boolean\InstanceOf_::class,
        'LogicalAnd' => Mutator\Boolean\LogicalAnd::class,
        'LogicalLowerAnd' => Mutator\Boolean\LogicalLowerAnd::class,
        'LogicalLowerOr' => Mutator\Boolean\LogicalLowerOr::class,
        'LogicalNot' => Mutator\Boolean\LogicalNot::class,
        'LogicalOr' => Mutator\Boolean\LogicalOr::class,
        'NotEqualNotIdentical' => Mutator\Boolean\NotEqualNotIdentical::class,
        'NotIdenticalNotEqual' => Mutator\Boolean\NotIdenticalNotEqual::class,
        'TrueValue' => Mutator\Boolean\TrueValue::class,
        'Yield_' => Mutator\Boolean\Yield_::class,

        // Conditional Boundary
        'GreaterThan' => Mutator\ConditionalBoundary\GreaterThan::class,
        'GreaterThanOrEqualTo' => Mutator\ConditionalBoundary\GreaterThanOrEqualTo::class,
        'LessThan' => Mutator\ConditionalBoundary\LessThan::class,
        'LessThanOrEqualTo' => Mutator\ConditionalBoundary\LessThanOrEqualTo::class,

        // Conditional Negotiation
        'Equal' => Mutator\ConditionalNegotiation\Equal::class,
        'GreaterThanNegotiation' => Mutator\ConditionalNegotiation\GreaterThanNegotiation::class,
        'GreaterThanOrEqualToNegotiation' => Mutator\ConditionalNegotiation\GreaterThanOrEqualToNegotiation::class,
        'Identical' => Mutator\ConditionalNegotiation\Identical::class,
        'LessThanNegotiation' => Mutator\ConditionalNegotiation\LessThanNegotiation::class,
        'LessThanOrEqualToNegotiation' => Mutator\ConditionalNegotiation\LessThanOrEqualToNegotiation::class,
        'NotEqual' => Mutator\ConditionalNegotiation\NotEqual::class,
        'NotIdentical' => Mutator\ConditionalNegotiation\NotIdentical::class,

        // Function Signature
        'ProtectedVisibility' => Mutator\FunctionSignature\ProtectedVisibility::class,
        'PublicVisibility' => Mutator\FunctionSignature\PublicVisibility::class,

        // Number
        'DecrementInteger' => Mutator\Number\DecrementInteger::class,
        'IncrementInteger' => Mutator\Number\IncrementInteger::class,
        'OneZeroFloat' => Mutator\Number\OneZeroFloat::class,

        // Operator
        'AssignCoalesce' => Mutator\Operator\AssignCoalesce::class,
        'Break_' => Mutator\Operator\Break_::class,
        'Coalesce' => Mutator\Operator\Coalesce::class,
        'Concat' => Mutator\Operator\Concat::class,
        'Continue_' => Mutator\Operator\Continue_::class,
        'Finally_' => Mutator\Operator\Finally_::class,
        'NullSafeMethodCall' => Mutator\Operator\NullSafeMethodCall::class,
        'NullSafePropertyCall' => Mutator\Operator\NullSafePropertyCall::class,
        'SpreadAssignment' => Mutator\Operator\SpreadAssignment::class,
        'SpreadOneItem' => Mutator\Operator\SpreadOneItem::class,
        'SpreadRemoval' => Mutator\Operator\SpreadRemoval::class,
        'Ternary' => Mutator\Operator\Ternary::class,
        'Throw_' => Mutator\Operator\Throw_::class,

        // Regex
        'PregMatchMatches' => Mutator\Regex\PregMatchMatches::class,
        'PregMatchRemoveCaret' => Mutator\Regex\PregMatchRemoveCaret::class,
        'PregMatchRemoveDollar' => Mutator\Regex\PregMatchRemoveDollar::class,
        'PregMatchRemoveFlags' => Mutator\Regex\PregMatchRemoveFlags::class,
        'PregQuote' => Mutator\Regex\PregQuote::class,

        // Removal
        'ArrayItemRemoval' => Mutator\Removal\ArrayItemRemoval::class,
        'CloneRemoval' => Mutator\Removal\CloneRemoval::class,
        'ConcatOperandRemoval' => Mutator\Removal\ConcatOperandRemoval::class,
        'FunctionCallRemoval' => Mutator\Removal\FunctionCallRemoval::class,
        'MethodCallRemoval' => Mutator\Removal\MethodCallRemoval::class,
        'SharedCaseRemoval' => Mutator\Removal\SharedCaseRemoval::class,

        // Return Value
        'ArrayOneItem' => Mutator\ReturnValue\ArrayOneItem::class,
        'FloatNegation' => Mutator\ReturnValue\FloatNegation::class,
        'FunctionCall' => Mutator\ReturnValue\FunctionCall::class,
        'IntegerNegation' => Mutator\ReturnValue\IntegerNegation::class,
        'NewObject' => Mutator\ReturnValue\NewObject::class,
        'This' => Mutator\ReturnValue\This::class,
        'YieldValue' => Mutator\ReturnValue\YieldValue::class,

        // Sort
        'Spaceship' => Mutator\Sort\Spaceship::class,

        // Loop
        'DoWhile' => Mutator\Loop\DoWhile::class,
        'Foreach_' => Mutator\Loop\Foreach_::class,
        'For_' => Mutator\Loop\For_::class,
        'While_' => Mutator\Loop\While_::class,

        // Cast
        'CastArray' => Mutator\Cast\CastArray::class,
        'CastBool' => Mutator\Cast\CastBool::class,
        'CastFloat' => Mutator\Cast\CastFloat::class,
        'CastInt' => Mutator\Cast\CastInt::class,
        'CastObject' => Mutator\Cast\CastObject::class,
        'CastString' => Mutator\Cast\CastString::class,

        // Unwrap
        'UnwrapArrayChangeKeyCase' => Mutator\Unwrap\UnwrapArrayChangeKeyCase::class,
        'UnwrapArrayChunk' => Mutator\Unwrap\UnwrapArrayChunk::class,
        'UnwrapArrayColumn' => Mutator\Unwrap\UnwrapArrayColumn::class,
        'UnwrapArrayCombine' => Mutator\Unwrap\UnwrapArrayCombine::class,
        'UnwrapArrayDiff' => Mutator\Unwrap\UnwrapArrayDiff::class,
        'UnwrapArrayDiffAssoc' => Mutator\Unwrap\UnwrapArrayDiffAssoc::class,
        'UnwrapArrayDiffKey' => Mutator\Unwrap\UnwrapArrayDiffKey::class,
        'UnwrapArrayDiffUassoc' => Mutator\Unwrap\UnwrapArrayDiffUassoc::class,
        'UnwrapArrayDiffUkey' => Mutator\Unwrap\UnwrapArrayDiffUkey::class,
        'UnwrapArrayFilter' => Mutator\Unwrap\UnwrapArrayFilter::class,
        'UnwrapArrayFlip' => Mutator\Unwrap\UnwrapArrayFlip::class,
        'UnwrapArrayIntersect' => Mutator\Unwrap\UnwrapArrayIntersect::class,
        'UnwrapArrayIntersectAssoc' => Mutator\Unwrap\UnwrapArrayIntersectAssoc::class,
        'UnwrapArrayIntersectKey' => Mutator\Unwrap\UnwrapArrayIntersectKey::class,
        'UnwrapArrayIntersectUassoc' => Mutator\Unwrap\UnwrapArrayIntersectUassoc::class,
        'UnwrapArrayIntersectUkey' => Mutator\Unwrap\UnwrapArrayIntersectUkey::class,
        'UnwrapArrayKeys' => Mutator\Unwrap\UnwrapArrayKeys::class,
        'UnwrapArrayMap' => Mutator\Unwrap\UnwrapArrayMap::class,
        'UnwrapArrayMerge' => Mutator\Unwrap\UnwrapArrayMerge::class,
        'UnwrapArrayMergeRecursive' => Mutator\Unwrap\UnwrapArrayMergeRecursive::class,
        'UnwrapArrayPad' => Mutator\Unwrap\UnwrapArrayPad::class,
        'UnwrapArrayReduce' => Mutator\Unwrap\UnwrapArrayReduce::class,
        'UnwrapArrayReplace' => Mutator\Unwrap\UnwrapArrayReplace::class,
        'UnwrapArrayReplaceRecursive' => Mutator\Unwrap\UnwrapArrayReplaceRecursive::class,
        'UnwrapArrayReverse' => Mutator\Unwrap\UnwrapArrayReverse::class,
        'UnwrapArraySlice' => Mutator\Unwrap\UnwrapArraySlice::class,
        'UnwrapArraySplice' => Mutator\Unwrap\UnwrapArraySplice::class,
        'UnwrapArrayUdiff' => Mutator\Unwrap\UnwrapArrayUdiff::class,
        'UnwrapArrayUdiffAssoc' => Mutator\Unwrap\UnwrapArrayUdiffAssoc::class,
        'UnwrapArrayUdiffUassoc' => Mutator\Unwrap\UnwrapArrayUdiffUassoc::class,
        'UnwrapArrayUintersect' => Mutator\Unwrap\UnwrapArrayUintersect::class,
        'UnwrapArrayUintersectAssoc' => Mutator\Unwrap\UnwrapArrayUintersectAssoc::class,
        'UnwrapArrayUintersectUassoc' => Mutator\Unwrap\UnwrapArrayUintersectUassoc::class,
        'UnwrapArrayUnique' => Mutator\Unwrap\UnwrapArrayUnique::class,
        'UnwrapArrayValues' => Mutator\Unwrap\UnwrapArrayValues::class,
        'UnwrapLcFirst' => Mutator\Unwrap\UnwrapLcFirst::class,
        'UnwrapLtrim' => Mutator\Unwrap\UnwrapLtrim::class,
        'UnwrapRtrim' => Mutator\Unwrap\UnwrapRtrim::class,
        'UnwrapStrIreplace' => Mutator\Unwrap\UnwrapStrIreplace::class,
        'UnwrapStrRepeat' => Mutator\Unwrap\UnwrapStrRepeat::class,
        'UnwrapStrReplace' => Mutator\Unwrap\UnwrapStrReplace::class,
        'UnwrapStrRev' => Mutator\Unwrap\UnwrapStrRev::class,
        'UnwrapStrShuffle' => Mutator\Unwrap\UnwrapStrShuffle::class,
        'UnwrapStrToLower' => Mutator\Unwrap\UnwrapStrToLower::class,
        'UnwrapStrToUpper' => Mutator\Unwrap\UnwrapStrToUpper::class,
        'UnwrapSubstr' => Mutator\Unwrap\UnwrapSubstr::class,
        'UnwrapTrim' => Mutator\Unwrap\UnwrapTrim::class,
        'UnwrapUcFirst' => Mutator\Unwrap\UnwrapUcFirst::class,
        'UnwrapUcWords' => Mutator\Unwrap\UnwrapUcWords::class,

        // Extensions
        'BCMath' => Mutator\Extensions\BCMath::class,
        'MBString' => Mutator\Extensions\MBString::class,

        // Internal usage only
        'SyntaxError' => Mutator\SyntaxError::class,
    ];

    /** @var array<string, string>|null */
    private static ?array $defaultProfileMutators = null;

    /**
     * @return array<int, string>
     */
    public static function getDefaultProfileMutators(): array
    {
        if (self::$defaultProfileMutators !== null) {
            return array_values(self::$defaultProfileMutators);
        }

        self::$defaultProfileMutators = [];

        foreach (self::DEFAULT_PROFILE as $profile) {
            foreach (self::ALL_PROFILES[$profile] as $mutatorClass) {
                self::$defaultProfileMutators[$mutatorClass] = $mutatorClass;
            }
        }

        return array_values(self::$defaultProfileMutators);
    }
}
