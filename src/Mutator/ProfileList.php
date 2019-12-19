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

use Infection\Mutator\Arithmetic\Assignment;
use Infection\Mutator\Arithmetic\AssignmentEqual;
use Infection\Mutator\Arithmetic\BitwiseAnd;
use Infection\Mutator\Arithmetic\BitwiseNot;
use Infection\Mutator\Arithmetic\BitwiseOr;
use Infection\Mutator\Arithmetic\BitwiseXor;
use Infection\Mutator\Arithmetic\Decrement;
use Infection\Mutator\Arithmetic\DivEqual;
use Infection\Mutator\Arithmetic\Division;
use Infection\Mutator\Arithmetic\Exponentiation;
use Infection\Mutator\Arithmetic\Increment;
use Infection\Mutator\Arithmetic\Minus;
use Infection\Mutator\Arithmetic\MinusEqual;
use Infection\Mutator\Arithmetic\ModEqual;
use Infection\Mutator\Arithmetic\Modulus;
use Infection\Mutator\Arithmetic\MulEqual;
use Infection\Mutator\Arithmetic\Multiplication;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Arithmetic\PlusEqual;
use Infection\Mutator\Arithmetic\PowEqual;
use Infection\Mutator\Arithmetic\RoundingFamily;
use Infection\Mutator\Arithmetic\ShiftLeft;
use Infection\Mutator\Arithmetic\ShiftRight;
use Infection\Mutator\Boolean\ArrayItem;
use Infection\Mutator\Boolean\FalseValue;
use Infection\Mutator\Boolean\LogicalAnd;
use Infection\Mutator\Boolean\LogicalLowerAnd;
use Infection\Mutator\Boolean\LogicalLowerOr;
use Infection\Mutator\Boolean\LogicalNot;
use Infection\Mutator\Boolean\LogicalOr;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\Boolean\Yield_;
use Infection\Mutator\ConditionalBoundary\GreaterThan;
use Infection\Mutator\ConditionalBoundary\GreaterThanOrEqualTo;
use Infection\Mutator\ConditionalBoundary\LessThan;
use Infection\Mutator\ConditionalBoundary\LessThanOrEqualTo;
use Infection\Mutator\ConditionalNegotiation\Equal;
use Infection\Mutator\ConditionalNegotiation\GreaterThanNegotiation;
use Infection\Mutator\ConditionalNegotiation\GreaterThanOrEqualToNegotiation;
use Infection\Mutator\ConditionalNegotiation\Identical;
use Infection\Mutator\ConditionalNegotiation\LessThanNegotiation;
use Infection\Mutator\ConditionalNegotiation\LessThanOrEqualToNegotiation;
use Infection\Mutator\ConditionalNegotiation\NotEqual;
use Infection\Mutator\ConditionalNegotiation\NotIdentical;
use Infection\Mutator\Boolean\IdenticalEqual;
use Infection\Mutator\Boolean\NotIdenticalNotEqual;
use Infection\Mutator\FunctionSignature\ProtectedVisibility;
use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\Mutator\Boolean\EqualIdentical;
use Infection\Mutator\Boolean\NotEqualNotIdentical;
use Infection\Mutator\Number\DecrementInteger;
use Infection\Mutator\Number\IncrementInteger;
use Infection\Mutator\Number\OneZeroFloat;
use Infection\Mutator\Number\OneZeroInteger;
use Infection\Mutator\Operator\AssignCoalesce;
use Infection\Mutator\Operator\Break_;
use Infection\Mutator\Operator\Coalesce;
use Infection\Mutator\Operator\Continue_;
use Infection\Mutator\Operator\Finally_;
use Infection\Mutator\Operator\Spread;
use Infection\Mutator\Operator\Throw_;
use Infection\Mutator\Regex\PregMatchMatches;
use Infection\Mutator\Regex\PregQuote;
use Infection\Mutator\Removal\ArrayItemRemoval;
use Infection\Mutator\Removal\CloneRemoval;
use Infection\Mutator\Removal\FunctionCallRemoval;
use Infection\Mutator\Removal\MethodCallRemoval;
use Infection\Mutator\ReturnValue\ArrayOneItem;
use Infection\Mutator\ReturnValue\FloatNegation;
use Infection\Mutator\ReturnValue\FunctionCall;
use Infection\Mutator\ReturnValue\IntegerNegation;
use Infection\Mutator\ReturnValue\NewObject;
use Infection\Mutator\ReturnValue\This;
use Infection\Mutator\Sort\Spaceship;
use Infection\Mutator\ZeroIteration\For_;
use Infection\Mutator\ZeroIteration\Foreach_;
use Infection\Mutator\Cast\CastArray;
use Infection\Mutator\Cast\CastBool;
use Infection\Mutator\Cast\CastFloat;
use Infection\Mutator\Cast\CastInt;
use Infection\Mutator\Cast\CastObject;
use Infection\Mutator\Cast\CastString;
use Infection\Mutator\Unwrap\UnwrapArrayChangeKeyCase;
use Infection\Mutator\Unwrap\UnwrapArrayChunk;
use Infection\Mutator\Unwrap\UnwrapArrayColumn;
use Infection\Mutator\Unwrap\UnwrapArrayCombine;
use Infection\Mutator\Unwrap\UnwrapArrayDiff;
use Infection\Mutator\Unwrap\UnwrapArrayDiffAssoc;
use Infection\Mutator\Unwrap\UnwrapArrayDiffKey;
use Infection\Mutator\Unwrap\UnwrapArrayDiffUassoc;
use Infection\Mutator\Unwrap\UnwrapArrayDiffUkey;
use Infection\Mutator\Unwrap\UnwrapArrayFilter;
use Infection\Mutator\Unwrap\UnwrapArrayFlip;
use Infection\Mutator\Unwrap\UnwrapArrayIntersect;
use Infection\Mutator\Unwrap\UnwrapArrayIntersectAssoc;
use Infection\Mutator\Unwrap\UnwrapArrayIntersectKey;
use Infection\Mutator\Unwrap\UnwrapArrayIntersectUassoc;
use Infection\Mutator\Unwrap\UnwrapArrayIntersectUkey;
use Infection\Mutator\Unwrap\UnwrapArrayKeys;
use Infection\Mutator\Unwrap\UnwrapArrayMap;
use Infection\Mutator\Unwrap\UnwrapArrayMerge;
use Infection\Mutator\Unwrap\UnwrapArrayMergeRecursive;
use Infection\Mutator\Unwrap\UnwrapArrayPad;
use Infection\Mutator\Unwrap\UnwrapArrayReduce;
use Infection\Mutator\Unwrap\UnwrapArrayReplace;
use Infection\Mutator\Unwrap\UnwrapArrayReplaceRecursive;
use Infection\Mutator\Unwrap\UnwrapArrayReverse;
use Infection\Mutator\Unwrap\UnwrapArraySlice;
use Infection\Mutator\Unwrap\UnwrapArraySplice;
use Infection\Mutator\Unwrap\UnwrapArrayUdiff;
use Infection\Mutator\Unwrap\UnwrapArrayUdiffAssoc;
use Infection\Mutator\Unwrap\UnwrapArrayUdiffUassoc;
use Infection\Mutator\Unwrap\UnwrapArrayUintersect;
use Infection\Mutator\Unwrap\UnwrapArrayUintersectAssoc;
use Infection\Mutator\Unwrap\UnwrapArrayUintersectUassoc;
use Infection\Mutator\Unwrap\UnwrapArrayUnique;
use Infection\Mutator\Unwrap\UnwrapArrayValues;
use Infection\Mutator\Unwrap\UnwrapLcFirst;
use Infection\Mutator\Unwrap\UnwrapStrRepeat;
use Infection\Mutator\Unwrap\UnwrapStrReplace;
use Infection\Mutator\Unwrap\UnwrapStrToLower;
use Infection\Mutator\Unwrap\UnwrapStrToUpper;
use Infection\Mutator\Unwrap\UnwrapTrim;
use Infection\Mutator\Unwrap\UnwrapUcFirst;
use Infection\Mutator\Unwrap\UnwrapUcWords;
use Infection\Mutator\Extensions\BCMath;
use Infection\Mutator\Extensions\MBString;
use function array_values;
use Infection\Mutator;

/**
 * @internal
 */
final class ProfileList
{
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
        '@number' => self::NUMBER_PROFILE,
        '@operator' => self::OPERATOR_PROFILE,
        '@regex' => self::REGEX_PROFILE,
        '@removal' => self::REMOVAL_PROFILE,
        '@return_value' => self::RETURN_VALUE_PROFILE,
        '@sort' => self::SORT_PROFILE,
        '@unwrap' => self::UNWRAP_PROFILE,
        '@zero_iteration' => self::ZERO_ITERATION_PROFILE,
    ];

    public const ARITHMETIC_PROFILE = [
        Assignment::class,
        AssignmentEqual::class,
        BitwiseAnd::class,
        BitwiseNot::class,
        BitwiseOr::class,
        BitwiseXor::class,
        Decrement::class,
        DivEqual::class,
        Division::class,
        Exponentiation::class,
        Increment::class,
        Minus::class,
        MinusEqual::class,
        ModEqual::class,
        Modulus::class,
        MulEqual::class,
        Multiplication::class,
        Plus::class,
        PlusEqual::class,
        PowEqual::class,
        RoundingFamily::class,
        ShiftLeft::class,
        ShiftRight::class,
    ];

    public const BOOLEAN_PROFILE = [
        ArrayItem::class,
        // EqualIdentical disabled from the default boolean profile
        FalseValue::class,
        // IdenticalEqual disabled from the default boolean profile
        LogicalAnd::class,
        LogicalLowerAnd::class,
        LogicalLowerOr::class,
        LogicalNot::class,
        LogicalOr::class,
        // NotEqualNotIdentical disabled from the default boolean profile
        // NotIdenticalNotEqual disabled from the default boolean profile
        TrueValue::class,
        Yield_::class,
    ];

    public const CONDITIONAL_BOUNDARY_PROFILE = [
        GreaterThan::class,
        GreaterThanOrEqualTo::class,
        LessThan::class,
        LessThanOrEqualTo::class,
    ];

    public const CONDITIONAL_NEGOTIATION_PROFILE = [
        Equal::class,
        GreaterThanNegotiation::class,
        GreaterThanOrEqualToNegotiation::class,
        Identical::class,
        LessThanNegotiation::class,
        LessThanOrEqualToNegotiation::class,
        NotEqual::class,
        NotIdentical::class,
    ];

    public const EQUAL_PROFILE = [
        IdenticalEqual::class,
        NotIdenticalNotEqual::class,
    ];

    public const FUNCTION_SIGNATURE_PROFILE = [
        ProtectedVisibility::class,
        PublicVisibility::class,
    ];

    public const IDENTICAL_PROFILE = [
        EqualIdentical::class,
        NotEqualNotIdentical::class,
    ];

    public const NUMBER_PROFILE = [
        DecrementInteger::class,
        IncrementInteger::class,
        OneZeroFloat::class,
        OneZeroInteger::class,
    ];

    public const OPERATOR_PROFILE = [
        AssignCoalesce::class,
        Break_::class,
        Coalesce::class,
        Continue_::class,
        Finally_::class,
        Spread::class,
        Throw_::class,
    ];

    public const REGEX_PROFILE = [
        PregMatchMatches::class,
        PregQuote::class,
    ];

    public const REMOVAL_PROFILE = [
        ArrayItemRemoval::class,
        CloneRemoval::class,
        FunctionCallRemoval::class,
        MethodCallRemoval::class,
    ];

    public const RETURN_VALUE_PROFILE = [
        ArrayOneItem::class,
        FloatNegation::class,
        FunctionCall::class,
        IntegerNegation::class,
        NewObject::class,
        This::class,
    ];

    public const SORT_PROFILE = [
        Spaceship::class,
    ];

    public const ZERO_ITERATION_PROFILE = [
        For_::class,
        Foreach_::class,
    ];

    public const CAST_PROFILE = [
        CastArray::class,
        CastBool::class,
        CastFloat::class,
        CastInt::class,
        CastObject::class,
        CastString::class,
    ];

    public const UNWRAP_PROFILE = [
        UnwrapArrayChangeKeyCase::class,
        UnwrapArrayChunk::class,
        UnwrapArrayColumn::class,
        UnwrapArrayCombine::class,
        UnwrapArrayDiff::class,
        UnwrapArrayDiffAssoc::class,
        UnwrapArrayDiffKey::class,
        UnwrapArrayDiffUassoc::class,
        UnwrapArrayDiffUkey::class,
        UnwrapArrayFilter::class,
        UnwrapArrayFlip::class,
        UnwrapArrayIntersect::class,
        UnwrapArrayIntersectAssoc::class,
        UnwrapArrayIntersectKey::class,
        UnwrapArrayIntersectUassoc::class,
        UnwrapArrayIntersectUkey::class,
        UnwrapArrayKeys::class,
        UnwrapArrayMap::class,
        UnwrapArrayMerge::class,
        UnwrapArrayMergeRecursive::class,
        UnwrapArrayPad::class,
        UnwrapArrayReduce::class,
        UnwrapArrayReplace::class,
        UnwrapArrayReplaceRecursive::class,
        UnwrapArrayReverse::class,
        UnwrapArraySlice::class,
        UnwrapArraySplice::class,
        UnwrapArrayUdiff::class,
        UnwrapArrayUdiffAssoc::class,
        UnwrapArrayUdiffUassoc::class,
        UnwrapArrayUintersect::class,
        UnwrapArrayUintersectAssoc::class,
        UnwrapArrayUintersectUassoc::class,
        UnwrapArrayUnique::class,
        UnwrapArrayValues::class,
        UnwrapLcFirst::class,
        UnwrapStrRepeat::class,
        UnwrapStrReplace::class,
        UnwrapStrToLower::class,
        UnwrapStrToUpper::class,
        UnwrapTrim::class,
        UnwrapUcFirst::class,
        UnwrapUcWords::class,
    ];

    public const EXTENSIONS_PROFILE = [
        BCMath::class,
        MBString::class,
    ];

    public const DEFAULT_PROFILE = [
        '@arithmetic',
        '@boolean',
        '@cast',
        '@conditional_boundary',
        '@conditional_negotiation',
        '@extensions',
        '@function_signature',
        '@number',
        '@operator',
        '@regex',
        '@removal',
        '@return_value',
        '@sort',
        '@unwrap',
        '@zero_iteration',
    ];

    public const ALL_MUTATORS = [
        // Arithmetic
        'Assignment' => Assignment::class,
        'AssignmentEqual' => AssignmentEqual::class,
        'BitwiseAnd' => BitwiseAnd::class,
        'BitwiseNot' => BitwiseNot::class,
        'BitwiseOr' => BitwiseOr::class,
        'BitwiseXor' => BitwiseXor::class,
        'Decrement' => Decrement::class,
        'DivEqual' => DivEqual::class,
        'Division' => Division::class,
        'Exponentiation' => Exponentiation::class,
        'Increment' => Increment::class,
        'Minus' => Minus::class,
        'MinusEqual' => MinusEqual::class,
        'ModEqual' => ModEqual::class,
        'Modulus' => Modulus::class,
        'MulEqual' => MulEqual::class,
        'Multiplication' => Multiplication::class,
        'Plus' => Plus::class,
        'PlusEqual' => PlusEqual::class,
        'PowEqual' => PowEqual::class,
        'RoundingFamily' => RoundingFamily::class,
        'ShiftLeft' => ShiftLeft::class,
        'ShiftRight' => ShiftRight::class,

        // Boolean
        'ArrayItem' => ArrayItem::class,
        'EqualIdentical' => EqualIdentical::class,
        'FalseValue' => FalseValue::class,
        'IdenticalEqual' => IdenticalEqual::class,
        'LogicalAnd' => LogicalAnd::class,
        'LogicalLowerAnd' => LogicalLowerAnd::class,
        'LogicalLowerOr' => LogicalLowerOr::class,
        'LogicalNot' => LogicalNot::class,
        'LogicalOr' => LogicalOr::class,
        'NotEqualNotIdentical' => NotEqualNotIdentical::class,
        'NotIdenticalNotEqual' => NotIdenticalNotEqual::class,
        'TrueValue' => TrueValue::class,
        'Yield_' => Yield_::class,

        // Conditional Boundary
        'GreaterThan' => GreaterThan::class,
        'GreaterThanOrEqualTo' => GreaterThanOrEqualTo::class,
        'LessThan' => LessThan::class,
        'LessThanOrEqualTo' => LessThanOrEqualTo::class,

        // Conditional Negotiation
        'Equal' => Equal::class,
        'GreaterThanNegotiation' => GreaterThanNegotiation::class,
        'GreaterThanOrEqualToNegotiation' => GreaterThanOrEqualToNegotiation::class,
        'Identical' => Identical::class,
        'LessThanNegotiation' => LessThanNegotiation::class,
        'LessThanOrEqualToNegotiation' => LessThanOrEqualToNegotiation::class,
        'NotEqual' => NotEqual::class,
        'NotIdentical' => NotIdentical::class,

        // Function Signature
        'ProtectedVisibility' => ProtectedVisibility::class,
        'PublicVisibility' => PublicVisibility::class,

        // Number
        'DecrementInteger' => DecrementInteger::class,
        'IncrementInteger' => IncrementInteger::class,
        'OneZeroFloat' => OneZeroFloat::class,
        'OneZeroInteger' => OneZeroInteger::class,

        // Operator
        'AssignCoalesce' => AssignCoalesce::class,
        'Break_' => Break_::class,
        'Coalesce' => Coalesce::class,
        'Continue_' => Continue_::class,
        'Finally_' => Finally_::class,
        'Spread' => Spread::class,
        'Throw_' => Throw_::class,

        // Regex
        'PregMatchMatches' => PregMatchMatches::class,
        'PregQuote' => PregQuote::class,

        // Removal
        'ArrayItemRemoval' => ArrayItemRemoval::class,
        'CloneRemoval' => CloneRemoval::class,
        'FunctionCallRemoval' => FunctionCallRemoval::class,
        'MethodCallRemoval' => MethodCallRemoval::class,

        // Return Value
        'ArrayOneItem' => ArrayOneItem::class,
        'FloatNegation' => FloatNegation::class,
        'FunctionCall' => FunctionCall::class,
        'IntegerNegation' => IntegerNegation::class,
        'NewObject' => NewObject::class,
        'This' => This::class,

        // Sort
        'Spaceship' => Spaceship::class,

        // Zero Iteration
        'Foreach_' => Foreach_::class,
        'For_' => For_::class,

        // Cast
        'CastArray' => CastArray::class,
        'CastBool' => CastBool::class,
        'CastFloat' => CastFloat::class,
        'CastInt' => CastInt::class,
        'CastObject' => CastObject::class,
        'CastString' => CastString::class,

        // Unwrap
        'UnwrapArrayChangeKeyCase' => UnwrapArrayChangeKeyCase::class,
        'UnwrapArrayChunk' => UnwrapArrayChunk::class,
        'UnwrapArrayColumn' => UnwrapArrayColumn::class,
        'UnwrapArrayCombine' => UnwrapArrayCombine::class,
        'UnwrapArrayDiff' => UnwrapArrayDiff::class,
        'UnwrapArrayDiffAssoc' => UnwrapArrayDiffAssoc::class,
        'UnwrapArrayDiffKey' => UnwrapArrayDiffKey::class,
        'UnwrapArrayDiffUassoc' => UnwrapArrayDiffUassoc::class,
        'UnwrapArrayDiffUkey' => UnwrapArrayDiffUkey::class,
        'UnwrapArrayFilter' => UnwrapArrayFilter::class,
        'UnwrapArrayFlip' => UnwrapArrayFlip::class,
        'UnwrapArrayIntersect' => UnwrapArrayIntersect::class,
        'UnwrapArrayIntersectAssoc' => UnwrapArrayIntersectAssoc::class,
        'UnwrapArrayIntersectKey' => UnwrapArrayIntersectKey::class,
        'UnwrapArrayIntersectUassoc' => UnwrapArrayIntersectUassoc::class,
        'UnwrapArrayIntersectUkey' => UnwrapArrayIntersectUkey::class,
        'UnwrapArrayKeys' => UnwrapArrayKeys::class,
        'UnwrapArrayMap' => UnwrapArrayMap::class,
        'UnwrapArrayMerge' => UnwrapArrayMerge::class,
        'UnwrapArrayMergeRecursive' => UnwrapArrayMergeRecursive::class,
        'UnwrapArrayPad' => UnwrapArrayPad::class,
        'UnwrapArrayReduce' => UnwrapArrayReduce::class,
        'UnwrapArrayReplace' => UnwrapArrayReplace::class,
        'UnwrapArrayReplaceRecursive' => UnwrapArrayReplaceRecursive::class,
        'UnwrapArrayReverse' => UnwrapArrayReverse::class,
        'UnwrapArraySlice' => UnwrapArraySlice::class,
        'UnwrapArraySplice' => UnwrapArraySplice::class,
        'UnwrapArrayUdiff' => UnwrapArrayUdiff::class,
        'UnwrapArrayUdiffAssoc' => UnwrapArrayUdiffAssoc::class,
        'UnwrapArrayUdiffUassoc' => UnwrapArrayUdiffUassoc::class,
        'UnwrapArrayUintersect' => UnwrapArrayUintersect::class,
        'UnwrapArrayUintersectAssoc' => UnwrapArrayUintersectAssoc::class,
        'UnwrapArrayUintersectUassoc' => UnwrapArrayUintersectUassoc::class,
        'UnwrapArrayUnique' => UnwrapArrayUnique::class,
        'UnwrapArrayValues' => UnwrapArrayValues::class,
        'UnwrapLcFirst' => UnwrapLcFirst::class,
        'UnwrapStrRepeat' => UnwrapStrRepeat::class,
        'UnwrapStrReplace' => UnwrapStrReplace::class,
        'UnwrapStrToLower' => UnwrapStrToLower::class,
        'UnwrapStrToUpper' => UnwrapStrToUpper::class,
        'UnwrapTrim' => UnwrapTrim::class,
        'UnwrapUcFirst' => UnwrapUcFirst::class,
        'UnwrapUcWords' => UnwrapUcWords::class,

        // Extensions
        'BCMath' => BCMath::class,
        'MBString' => MBString::class,
    ];

    /**
     * @var array<string, string>|null
     */
    private static $defaultProfileMutators;

    private function __construct()
    {
    }

    /**
     * @return string[]
     */
    public static function getDefaultProfileMutators(): array
    {
        if (null !== self::$defaultProfileMutators) {
            return self::$defaultProfileMutators;
        }

        self::$defaultProfileMutators = [];

        foreach (self::DEFAULT_PROFILE as $profile) {
            foreach (self::ALL_PROFILES[$profile] as $mutatorClass) {
                self::$defaultProfileMutators[$mutatorClass] = $mutatorClass;
            }
        }

        self::$defaultProfileMutators = array_values(self::$defaultProfileMutators);

        return self::$defaultProfileMutators;
    }
}
