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
        '@nullify' => self::NULLIFY_PROFILE,
        '@number' => self::NUMBER_PROFILE,
        '@operator' => self::OPERATOR_PROFILE,
        '@regex' => self::REGEX_PROFILE,
        '@removal' => self::REMOVAL_PROFILE,
        '@return_value' => self::RETURN_VALUE_PROFILE,
        '@sort' => self::SORT_PROFILE,
        '@unwrap' => self::UNWRAP_PROFILE,
    ];

    public const ARITHMETIC_PROFILE = [
        Arithmetic\Assignment::class,
        Arithmetic\AssignmentEqual::class,
        Arithmetic\BitwiseAnd::class,
        Arithmetic\BitwiseNot::class,
        Arithmetic\BitwiseOr::class,
        Arithmetic\BitwiseXor::class,
        Arithmetic\Decrement::class,
        Arithmetic\DivEqual::class,
        Arithmetic\Division::class,
        Arithmetic\Exponentiation::class,
        Arithmetic\Increment::class,
        Arithmetic\Minus::class,
        Arithmetic\MinusEqual::class,
        Arithmetic\ModEqual::class,
        Arithmetic\Modulus::class,
        Arithmetic\MulEqual::class,
        Arithmetic\Multiplication::class,
        Arithmetic\Plus::class,
        Arithmetic\PlusEqual::class,
        Arithmetic\PowEqual::class,
        Arithmetic\RoundingFamily::class,
        Arithmetic\ShiftLeft::class,
        Arithmetic\ShiftRight::class,
    ];

    public const BOOLEAN_PROFILE = [
        Boolean\ArrayAll::class,
        Boolean\ArrayAny::class,
        Boolean\ArrayItem::class,
        // EqualIdentical disabled from the default boolean profile
        Boolean\FalseValue::class,
        // IdenticalEqual disabled from the default boolean profile
        Boolean\InstanceOf_::class,
        Boolean\LogicalAnd::class,
        Boolean\LogicalAndAllSubExprNegation::class,
        Boolean\LogicalAndNegation::class,
        Boolean\LogicalAndSingleSubExprNegation::class,
        Boolean\LogicalLowerAnd::class,
        Boolean\LogicalLowerOr::class,
        Boolean\LogicalNot::class,
        Boolean\LogicalOr::class,
        Boolean\LogicalOrAllSubExprNegation::class,
        Boolean\LogicalOrNegation::class,
        Boolean\LogicalOrSingleSubExprNegation::class,
        // NotEqualNotIdentical disabled from the default boolean profile
        // NotIdenticalNotEqual disabled from the default boolean profile
        Boolean\TrueValue::class,
        Boolean\Yield_::class,
    ];

    public const CONDITIONAL_BOUNDARY_PROFILE = [
        ConditionalBoundary\GreaterThan::class,
        ConditionalBoundary\GreaterThanOrEqualTo::class,
        ConditionalBoundary\LessThan::class,
        ConditionalBoundary\LessThanOrEqualTo::class,
    ];

    public const CONDITIONAL_NEGOTIATION_PROFILE = [
        ConditionalNegotiation\Equal::class,
        ConditionalNegotiation\GreaterThanNegotiation::class,
        ConditionalNegotiation\GreaterThanOrEqualToNegotiation::class,
        ConditionalNegotiation\Identical::class,
        ConditionalNegotiation\LessThanNegotiation::class,
        ConditionalNegotiation\LessThanOrEqualToNegotiation::class,
        ConditionalNegotiation\NotEqual::class,
        ConditionalNegotiation\NotIdentical::class,
    ];

    public const EQUAL_PROFILE = [
        Boolean\IdenticalEqual::class,
        Boolean\NotIdenticalNotEqual::class,
    ];

    public const FUNCTION_SIGNATURE_PROFILE = [
        FunctionSignature\ProtectedVisibility::class,
        FunctionSignature\PublicVisibility::class,
    ];

    public const IDENTICAL_PROFILE = [
        Boolean\EqualIdentical::class,
        Boolean\NotEqualNotIdentical::class,
    ];

    public const NULLIFY_PROFILE = [
        Nullify\ArrayFind::class,
        Nullify\ArrayFindKey::class,
    ];

    public const NUMBER_PROFILE = [
        Number\DecrementInteger::class,
        Number\IncrementInteger::class,
        Number\OneZeroFloat::class,
    ];

    public const OPERATOR_PROFILE = [
        Operator\AssignCoalesce::class,
        Operator\Break_::class,
        Operator\Catch_::class,
        Operator\Coalesce::class,
        Operator\Concat::class,
        Operator\Continue_::class,
        Operator\ElseIfNegation::class,
        Operator\Finally_::class,
        Operator\IfNegation::class,
        Operator\NullSafeMethodCall::class,
        Operator\NullSafePropertyCall::class,
        Operator\SpreadAssignment::class,
        Operator\SpreadOneItem::class,
        Operator\SpreadRemoval::class,
        Operator\Ternary::class,
        Operator\Throw_::class,
    ];

    public const REGEX_PROFILE = [
        Regex\PregMatchMatches::class,
        Regex\PregMatchRemoveCaret::class,
        Regex\PregMatchRemoveDollar::class,
        Regex\PregMatchRemoveFlags::class,
        Regex\PregQuote::class,
    ];

    public const REMOVAL_PROFILE = [
        Removal\ArrayItemRemoval::class,
        Removal\CatchBlockRemoval::class,
        Removal\CloneRemoval::class,
        Removal\ConcatOperandRemoval::class,
        Removal\FunctionCallRemoval::class,
        Removal\MatchArmRemoval::class,
        Removal\MethodCallRemoval::class,
        Removal\SharedCaseRemoval::class,
    ];

    public const RETURN_VALUE_PROFILE = [
        ReturnValue\ArrayOneItem::class,
        ReturnValue\FloatNegation::class,
        ReturnValue\FunctionCall::class,
        ReturnValue\IntegerNegation::class,
        ReturnValue\NewObject::class,
        ReturnValue\This::class,
        ReturnValue\YieldValue::class,
    ];

    public const SORT_PROFILE = [
        Sort\Spaceship::class,
    ];

    public const LOOP_PROFILE = [
        Loop\DoWhile::class,
        Loop\For_::class,
        Loop\Foreach_::class,
        Loop\While_::class,
    ];

    public const CAST_PROFILE = [
        Cast\CastArray::class,
        Cast\CastBool::class,
        Cast\CastFloat::class,
        Cast\CastInt::class,
        Cast\CastObject::class,
        Cast\CastString::class,
    ];

    public const UNWRAP_PROFILE = [
        Unwrap\UnwrapArrayChangeKeyCase::class,
        Unwrap\UnwrapArrayChunk::class,
        Unwrap\UnwrapArrayColumn::class,
        Unwrap\UnwrapArrayCombine::class,
        Unwrap\UnwrapArrayDiff::class,
        Unwrap\UnwrapArrayDiffAssoc::class,
        Unwrap\UnwrapArrayDiffKey::class,
        Unwrap\UnwrapArrayDiffUassoc::class,
        Unwrap\UnwrapArrayDiffUkey::class,
        Unwrap\UnwrapArrayFilter::class,
        Unwrap\UnwrapArrayFlip::class,
        Unwrap\UnwrapArrayIntersect::class,
        Unwrap\UnwrapArrayIntersectAssoc::class,
        Unwrap\UnwrapArrayIntersectKey::class,
        Unwrap\UnwrapArrayIntersectUassoc::class,
        Unwrap\UnwrapArrayIntersectUkey::class,
        Unwrap\UnwrapArrayKeys::class,
        Unwrap\UnwrapArrayMap::class,
        Unwrap\UnwrapArrayMerge::class,
        Unwrap\UnwrapArrayMergeRecursive::class,
        Unwrap\UnwrapArrayPad::class,
        Unwrap\UnwrapArrayReduce::class,
        Unwrap\UnwrapArrayReplace::class,
        Unwrap\UnwrapArrayReplaceRecursive::class,
        Unwrap\UnwrapArrayReverse::class,
        Unwrap\UnwrapArraySlice::class,
        Unwrap\UnwrapArraySplice::class,
        Unwrap\UnwrapArrayUdiff::class,
        Unwrap\UnwrapArrayUdiffAssoc::class,
        Unwrap\UnwrapArrayUdiffUassoc::class,
        Unwrap\UnwrapArrayUintersect::class,
        Unwrap\UnwrapArrayUintersectAssoc::class,
        Unwrap\UnwrapArrayUintersectUassoc::class,
        Unwrap\UnwrapArrayUnique::class,
        Unwrap\UnwrapArrayValues::class,
        Unwrap\UnwrapFinally::class,
        Unwrap\UnwrapLcFirst::class,
        Unwrap\UnwrapLtrim::class,
        Unwrap\UnwrapRtrim::class,
        Unwrap\UnwrapStrIreplace::class,
        Unwrap\UnwrapStrRepeat::class,
        Unwrap\UnwrapStrReplace::class,
        Unwrap\UnwrapStrRev::class,
        Unwrap\UnwrapStrShuffle::class,
        Unwrap\UnwrapStrToLower::class,
        Unwrap\UnwrapStrToUpper::class,
        Unwrap\UnwrapSubstr::class,
        Unwrap\UnwrapTrim::class,
        Unwrap\UnwrapUcFirst::class,
        Unwrap\UnwrapUcWords::class,
    ];

    public const EXTENSIONS_PROFILE = [
        Extensions\BCMath::class,
        Extensions\MBString::class,
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
        'Assignment' => Arithmetic\Assignment::class,
        'AssignmentEqual' => Arithmetic\AssignmentEqual::class,
        'BitwiseAnd' => Arithmetic\BitwiseAnd::class,
        'BitwiseNot' => Arithmetic\BitwiseNot::class,
        'BitwiseOr' => Arithmetic\BitwiseOr::class,
        'BitwiseXor' => Arithmetic\BitwiseXor::class,
        'Decrement' => Arithmetic\Decrement::class,
        'DivEqual' => Arithmetic\DivEqual::class,
        'Division' => Arithmetic\Division::class,
        'Exponentiation' => Arithmetic\Exponentiation::class,
        'Increment' => Arithmetic\Increment::class,
        'Minus' => Arithmetic\Minus::class,
        'MinusEqual' => Arithmetic\MinusEqual::class,
        'ModEqual' => Arithmetic\ModEqual::class,
        'Modulus' => Arithmetic\Modulus::class,
        'MulEqual' => Arithmetic\MulEqual::class,
        'Multiplication' => Arithmetic\Multiplication::class,
        'Plus' => Arithmetic\Plus::class,
        'PlusEqual' => Arithmetic\PlusEqual::class,
        'PowEqual' => Arithmetic\PowEqual::class,
        'RoundingFamily' => Arithmetic\RoundingFamily::class,
        'ShiftLeft' => Arithmetic\ShiftLeft::class,
        'ShiftRight' => Arithmetic\ShiftRight::class,

        // Boolean
        'ArrayAll' => Boolean\ArrayAll::class,
        'ArrayAny' => Boolean\ArrayAny::class,
        'ArrayItem' => Boolean\ArrayItem::class,
        'EqualIdentical' => Boolean\EqualIdentical::class,
        'FalseValue' => Boolean\FalseValue::class,
        'IdenticalEqual' => Boolean\IdenticalEqual::class,
        'InstanceOf_' => Boolean\InstanceOf_::class,
        'LogicalAnd' => Boolean\LogicalAnd::class,
        'LogicalAndAllSubExprNegation' => Boolean\LogicalAndAllSubExprNegation::class,
        'LogicalAndNegation' => Boolean\LogicalAndNegation::class,
        'LogicalAndSingleSubExprNegation' => Boolean\LogicalAndSingleSubExprNegation::class,
        'LogicalLowerAnd' => Boolean\LogicalLowerAnd::class,
        'LogicalLowerOr' => Boolean\LogicalLowerOr::class,
        'LogicalNot' => Boolean\LogicalNot::class,
        'LogicalOr' => Boolean\LogicalOr::class,
        'LogicalOrAllSubExprNegation' => Boolean\LogicalOrAllSubExprNegation::class,
        'LogicalOrNegation' => Boolean\LogicalOrNegation::class,
        'LogicalOrSingleSubExprNegation' => Boolean\LogicalOrSingleSubExprNegation::class,
        'NotEqualNotIdentical' => Boolean\NotEqualNotIdentical::class,
        'NotIdenticalNotEqual' => Boolean\NotIdenticalNotEqual::class,
        'TrueValue' => Boolean\TrueValue::class,
        'Yield_' => Boolean\Yield_::class,

        // Conditional Boundary
        'GreaterThan' => ConditionalBoundary\GreaterThan::class,
        'GreaterThanOrEqualTo' => ConditionalBoundary\GreaterThanOrEqualTo::class,
        'LessThan' => ConditionalBoundary\LessThan::class,
        'LessThanOrEqualTo' => ConditionalBoundary\LessThanOrEqualTo::class,

        // Conditional Negotiation
        'Equal' => ConditionalNegotiation\Equal::class,
        'GreaterThanNegotiation' => ConditionalNegotiation\GreaterThanNegotiation::class,
        'GreaterThanOrEqualToNegotiation' => ConditionalNegotiation\GreaterThanOrEqualToNegotiation::class,
        'Identical' => ConditionalNegotiation\Identical::class,
        'LessThanNegotiation' => ConditionalNegotiation\LessThanNegotiation::class,
        'LessThanOrEqualToNegotiation' => ConditionalNegotiation\LessThanOrEqualToNegotiation::class,
        'NotEqual' => ConditionalNegotiation\NotEqual::class,
        'NotIdentical' => ConditionalNegotiation\NotIdentical::class,

        // Function Signature
        'ProtectedVisibility' => FunctionSignature\ProtectedVisibility::class,
        'PublicVisibility' => FunctionSignature\PublicVisibility::class,

        // Nullify
        'ArrayFind' => Nullify\ArrayFind::class,
        'ArrayFindKey' => Nullify\ArrayFindKey::class,

        // Number
        'DecrementInteger' => Number\DecrementInteger::class,
        'IncrementInteger' => Number\IncrementInteger::class,
        'OneZeroFloat' => Number\OneZeroFloat::class,

        // Operator
        'AssignCoalesce' => Operator\AssignCoalesce::class,
        'Break_' => Operator\Break_::class,
        'Coalesce' => Operator\Coalesce::class,
        'Concat' => Operator\Concat::class,
        'Continue_' => Operator\Continue_::class,
        'ElseIfNegation' => Operator\ElseIfNegation::class,
        'Finally_' => Operator\Finally_::class,
        'IfNegation' => Operator\IfNegation::class,
        'NullSafeMethodCall' => Operator\NullSafeMethodCall::class,
        'NullSafePropertyCall' => Operator\NullSafePropertyCall::class,
        'SpreadAssignment' => Operator\SpreadAssignment::class,
        'SpreadOneItem' => Operator\SpreadOneItem::class,
        'SpreadRemoval' => Operator\SpreadRemoval::class,
        'Ternary' => Operator\Ternary::class,
        'Throw_' => Operator\Throw_::class,
        'Catch_' => Operator\Catch_::class,

        // Regex
        'PregMatchMatches' => Regex\PregMatchMatches::class,
        'PregMatchRemoveCaret' => Regex\PregMatchRemoveCaret::class,
        'PregMatchRemoveDollar' => Regex\PregMatchRemoveDollar::class,
        'PregMatchRemoveFlags' => Regex\PregMatchRemoveFlags::class,
        'PregQuote' => Regex\PregQuote::class,

        // Removal
        'ArrayItemRemoval' => Removal\ArrayItemRemoval::class,
        'CatchBlockRemoval' => Removal\CatchBlockRemoval::class,
        'CloneRemoval' => Removal\CloneRemoval::class,
        'ConcatOperandRemoval' => Removal\ConcatOperandRemoval::class,
        'FunctionCallRemoval' => Removal\FunctionCallRemoval::class,
        'MatchArmRemoval' => Removal\MatchArmRemoval::class,
        'MethodCallRemoval' => Removal\MethodCallRemoval::class,
        'SharedCaseRemoval' => Removal\SharedCaseRemoval::class,

        // Return Value
        'ArrayOneItem' => ReturnValue\ArrayOneItem::class,
        'FloatNegation' => ReturnValue\FloatNegation::class,
        'FunctionCall' => ReturnValue\FunctionCall::class,
        'IntegerNegation' => ReturnValue\IntegerNegation::class,
        'NewObject' => ReturnValue\NewObject::class,
        'This' => ReturnValue\This::class,
        'YieldValue' => ReturnValue\YieldValue::class,

        // Sort
        'Spaceship' => Sort\Spaceship::class,

        // Loop
        'DoWhile' => Loop\DoWhile::class,
        'Foreach_' => Loop\Foreach_::class,
        'For_' => Loop\For_::class,
        'While_' => Loop\While_::class,

        // Cast
        'CastArray' => Cast\CastArray::class,
        'CastBool' => Cast\CastBool::class,
        'CastFloat' => Cast\CastFloat::class,
        'CastInt' => Cast\CastInt::class,
        'CastObject' => Cast\CastObject::class,
        'CastString' => Cast\CastString::class,

        // Unwrap
        'UnwrapArrayChangeKeyCase' => Unwrap\UnwrapArrayChangeKeyCase::class,
        'UnwrapArrayChunk' => Unwrap\UnwrapArrayChunk::class,
        'UnwrapArrayColumn' => Unwrap\UnwrapArrayColumn::class,
        'UnwrapArrayCombine' => Unwrap\UnwrapArrayCombine::class,
        'UnwrapArrayDiff' => Unwrap\UnwrapArrayDiff::class,
        'UnwrapArrayDiffAssoc' => Unwrap\UnwrapArrayDiffAssoc::class,
        'UnwrapArrayDiffKey' => Unwrap\UnwrapArrayDiffKey::class,
        'UnwrapArrayDiffUassoc' => Unwrap\UnwrapArrayDiffUassoc::class,
        'UnwrapArrayDiffUkey' => Unwrap\UnwrapArrayDiffUkey::class,
        'UnwrapArrayFilter' => Unwrap\UnwrapArrayFilter::class,
        'UnwrapArrayFlip' => Unwrap\UnwrapArrayFlip::class,
        'UnwrapArrayIntersect' => Unwrap\UnwrapArrayIntersect::class,
        'UnwrapArrayIntersectAssoc' => Unwrap\UnwrapArrayIntersectAssoc::class,
        'UnwrapArrayIntersectKey' => Unwrap\UnwrapArrayIntersectKey::class,
        'UnwrapArrayIntersectUassoc' => Unwrap\UnwrapArrayIntersectUassoc::class,
        'UnwrapArrayIntersectUkey' => Unwrap\UnwrapArrayIntersectUkey::class,
        'UnwrapArrayKeys' => Unwrap\UnwrapArrayKeys::class,
        'UnwrapArrayMap' => Unwrap\UnwrapArrayMap::class,
        'UnwrapArrayMerge' => Unwrap\UnwrapArrayMerge::class,
        'UnwrapArrayMergeRecursive' => Unwrap\UnwrapArrayMergeRecursive::class,
        'UnwrapArrayPad' => Unwrap\UnwrapArrayPad::class,
        'UnwrapArrayReduce' => Unwrap\UnwrapArrayReduce::class,
        'UnwrapArrayReplace' => Unwrap\UnwrapArrayReplace::class,
        'UnwrapArrayReplaceRecursive' => Unwrap\UnwrapArrayReplaceRecursive::class,
        'UnwrapArrayReverse' => Unwrap\UnwrapArrayReverse::class,
        'UnwrapArraySlice' => Unwrap\UnwrapArraySlice::class,
        'UnwrapArraySplice' => Unwrap\UnwrapArraySplice::class,
        'UnwrapArrayUdiff' => Unwrap\UnwrapArrayUdiff::class,
        'UnwrapArrayUdiffAssoc' => Unwrap\UnwrapArrayUdiffAssoc::class,
        'UnwrapArrayUdiffUassoc' => Unwrap\UnwrapArrayUdiffUassoc::class,
        'UnwrapArrayUintersect' => Unwrap\UnwrapArrayUintersect::class,
        'UnwrapArrayUintersectAssoc' => Unwrap\UnwrapArrayUintersectAssoc::class,
        'UnwrapArrayUintersectUassoc' => Unwrap\UnwrapArrayUintersectUassoc::class,
        'UnwrapArrayUnique' => Unwrap\UnwrapArrayUnique::class,
        'UnwrapArrayValues' => Unwrap\UnwrapArrayValues::class,
        'UnwrapLcFirst' => Unwrap\UnwrapLcFirst::class,
        'UnwrapLtrim' => Unwrap\UnwrapLtrim::class,
        'UnwrapRtrim' => Unwrap\UnwrapRtrim::class,
        'UnwrapStrIreplace' => Unwrap\UnwrapStrIreplace::class,
        'UnwrapStrRepeat' => Unwrap\UnwrapStrRepeat::class,
        'UnwrapStrReplace' => Unwrap\UnwrapStrReplace::class,
        'UnwrapStrRev' => Unwrap\UnwrapStrRev::class,
        'UnwrapStrShuffle' => Unwrap\UnwrapStrShuffle::class,
        'UnwrapStrToLower' => Unwrap\UnwrapStrToLower::class,
        'UnwrapStrToUpper' => Unwrap\UnwrapStrToUpper::class,
        'UnwrapSubstr' => Unwrap\UnwrapSubstr::class,
        'UnwrapTrim' => Unwrap\UnwrapTrim::class,
        'UnwrapUcFirst' => Unwrap\UnwrapUcFirst::class,
        'UnwrapUcWords' => Unwrap\UnwrapUcWords::class,
        'UnwrapFinally' => Unwrap\UnwrapFinally::class,

        // Extensions
        'BCMath' => Extensions\BCMath::class,
        'MBString' => Extensions\MBString::class,

        // Internal usage only
        'SyntaxError' => SyntaxError::class,
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
