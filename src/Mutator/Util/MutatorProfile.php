<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
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

namespace Infection\Mutator\Util;

use Infection\Mutator;

/**
 * @internal
 */
final class MutatorProfile
{
    public const MUTATOR_PROFILE_LIST = [
        //Per category
        '@arithmetic' => self::ARITHMETIC,
        '@boolean' => self::BOOLEAN,
        '@conditional_boundary' => self::CONDITIONAL_BOUNDARY,
        '@conditional_negotiation' => self::CONDITIONAL_NEGOTIATION,
        '@equal' => self::EQUAL,
        '@function_signature' => self::FUNCTION_SIGNATURE,
        '@identical' => self::IDENTICAL,
        '@number' => self::NUMBER,
        '@operator' => self::OPERATOR,
        '@regex' => self::REGEX,
        '@removal' => self::REMOVAL,
        '@return_value' => self::RETURN_VALUE,
        '@sort' => self::SORT,
        '@zero_iteration' => self::ZERO_ITERATION,
        '@cast' => self::CAST,
        '@unwrap' => self::UNWRAP,

        //Special Profiles
        '@default' => self::DEFAULT,
    ];

    public const ARITHMETIC = [
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
        Mutator\Arithmetic\ShiftLeft::class,
        Mutator\Arithmetic\ShiftRight::class,
        Mutator\Arithmetic\RoundingFamily::class,
    ];

    public const BOOLEAN = [
        Mutator\Boolean\ArrayItem::class,
        Mutator\Boolean\FalseValue::class,
        Mutator\Boolean\LogicalAnd::class,
        Mutator\Boolean\LogicalLowerAnd::class,
        Mutator\Boolean\LogicalLowerOr::class,
        Mutator\Boolean\LogicalNot::class,
        Mutator\Boolean\LogicalOr::class,
        Mutator\Boolean\TrueValue::class,
        Mutator\Boolean\Yield_::class,
    ];

    public const CONDITIONAL_BOUNDARY = [
        Mutator\ConditionalBoundary\GreaterThan::class,
        Mutator\ConditionalBoundary\GreaterThanOrEqualTo::class,
        Mutator\ConditionalBoundary\LessThan::class,
        Mutator\ConditionalBoundary\LessThanOrEqualTo::class,
    ];

    public const CONDITIONAL_NEGOTIATION = [
        Mutator\ConditionalNegotiation\Equal::class,
        Mutator\ConditionalNegotiation\GreaterThanNegotiation::class,
        Mutator\ConditionalNegotiation\GreaterThanOrEqualToNegotiation::class,
        Mutator\ConditionalNegotiation\Identical::class,
        Mutator\ConditionalNegotiation\LessThanNegotiation::class,
        Mutator\ConditionalNegotiation\LessThanOrEqualToNegotiation::class,
        Mutator\ConditionalNegotiation\NotEqual::class,
        Mutator\ConditionalNegotiation\NotIdentical::class,
    ];

    public const EQUAL = [
        Mutator\Boolean\NotIdenticalNotEqual::class,
        Mutator\Boolean\IdenticalEqual::class,
    ];

    public const FUNCTION_SIGNATURE = [
        Mutator\FunctionSignature\PublicVisibility::class,
        Mutator\FunctionSignature\ProtectedVisibility::class,
    ];

    public const IDENTICAL = [
        Mutator\Boolean\EqualIdentical::class,
        Mutator\Boolean\NotEqualNotIdentical::class,
    ];

    public const NUMBER = [
        Mutator\Number\DecrementInteger::class,
        Mutator\Number\IncrementInteger::class,
        Mutator\Number\OneZeroInteger::class,
        Mutator\Number\OneZeroFloat::class,
    ];

    public const OPERATOR = [
        Mutator\Operator\Break_::class,
        Mutator\Operator\Continue_::class,
        Mutator\Operator\Throw_::class,
        Mutator\Operator\Coalesce::class,
    ];

    public const REGEX = [
        Mutator\Regex\PregQuote::class,
        Mutator\Regex\PregMatchMatches::class,
    ];

    public const REMOVAL = [
        Mutator\Removal\FunctionCallRemoval::class,
        Mutator\Removal\MethodCallRemoval::class,
    ];

    public const RETURN_VALUE = [
        Mutator\ReturnValue\FloatNegation::class,
        Mutator\ReturnValue\FunctionCall::class,
        Mutator\ReturnValue\IntegerNegation::class,
        Mutator\ReturnValue\NewObject::class,
        Mutator\ReturnValue\This::class,
    ];

    public const SORT = [
        Mutator\Sort\Spaceship::class,
    ];

    public const ZERO_ITERATION = [
        Mutator\ZeroIteration\Foreach_::class,
        Mutator\ZeroIteration\For_::class,
    ];

    public const CAST = [
        Mutator\Cast\CastArray::class,
        Mutator\Cast\CastBool::class,
        Mutator\Cast\CastFloat::class,
        Mutator\Cast\CastInt::class,
        Mutator\Cast\CastObject::class,
        Mutator\Cast\CastString::class,
    ];

    public const UNWRAP = [
        Mutator\Unwrap\UnwrapArrayFilter::class,
        Mutator\Unwrap\UnwrapArrayFlip::class,
        Mutator\Unwrap\UnwrapArrayMap::class,
        Mutator\Unwrap\UnwrapArrayReduce::class,
        Mutator\Unwrap\UnwrapArrayReverse::class,
        Mutator\Unwrap\UnwrapStrRepeat::class,
    ];

    public const DEFAULT = [
        '@arithmetic',
        '@boolean',
        '@cast',
        '@conditional_boundary',
        '@conditional_negotiation',
        '@function_signature',
        '@number',
        '@operator',
        '@regex',
        '@removal',
        '@return_value',
        '@sort',
        '@zero_iteration',
    ];

    public const FULL_MUTATOR_LIST = [
        //Arithmetic
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
        'ShiftLeft' => Mutator\Arithmetic\ShiftLeft::class,
        'ShiftRight' => Mutator\Arithmetic\ShiftRight::class,
        'RoundingFamily' => Mutator\Arithmetic\RoundingFamily::class,

        //Boolean
        'ArrayItem' => Mutator\Boolean\ArrayItem::class,
        'EqualIdentical' => Mutator\Boolean\EqualIdentical::class,
        'FalseValue' => Mutator\Boolean\FalseValue::class,
        'IdenticalEqual' => Mutator\Boolean\IdenticalEqual::class,
        'LogicalAnd' => Mutator\Boolean\LogicalAnd::class,
        'LogicalLowerAnd' => Mutator\Boolean\LogicalLowerAnd::class,
        'LogicalLowerOr' => Mutator\Boolean\LogicalLowerOr::class,
        'LogicalNot' => Mutator\Boolean\LogicalNot::class,
        'LogicalOr' => Mutator\Boolean\LogicalOr::class,
        'NotEqualNotIdentical' => Mutator\Boolean\NotEqualNotIdentical::class,
        'NotIdenticalNotEqual' => Mutator\Boolean\NotIdenticalNotEqual::class,
        'TrueValue' => Mutator\Boolean\TrueValue::class,
        'Yield_' => Mutator\Boolean\Yield_::class,

        //Conditional Boundary
        'GreaterThan' => Mutator\ConditionalBoundary\GreaterThan::class,
        'GreaterThanOrEqualTo' => Mutator\ConditionalBoundary\GreaterThanOrEqualTo::class,
        'LessThan' => Mutator\ConditionalBoundary\LessThan::class,
        'LessThanOrEqualTo' => Mutator\ConditionalBoundary\LessThanOrEqualTo::class,

        //Conditional Negotiation
        'Equal' => Mutator\ConditionalNegotiation\Equal::class,
        'GreaterThanNegotiation' => Mutator\ConditionalNegotiation\GreaterThanNegotiation::class,
        'GreaterThanOrEqualToNegotiation' => Mutator\ConditionalNegotiation\GreaterThanOrEqualToNegotiation::class,
        'Identical' => Mutator\ConditionalNegotiation\Identical::class,
        'LessThanNegotiation' => Mutator\ConditionalNegotiation\LessThanNegotiation::class,
        'LessThanOrEqualToNegotiation' => Mutator\ConditionalNegotiation\LessThanOrEqualToNegotiation::class,
        'NotEqual' => Mutator\ConditionalNegotiation\NotEqual::class,
        'NotIdentical' => Mutator\ConditionalNegotiation\NotIdentical::class,

        //Function Signature
        'PublicVisibility' => Mutator\FunctionSignature\PublicVisibility::class,
        'ProtectedVisibility' => Mutator\FunctionSignature\ProtectedVisibility::class,

        //Number
        'DecrementInteger' => Mutator\Number\DecrementInteger::class,
        'IncrementInteger' => Mutator\Number\IncrementInteger::class,
        'OneZeroInteger' => Mutator\Number\OneZeroInteger::class,
        'OneZeroFloat' => Mutator\Number\OneZeroFloat::class,

        //Operator
        'Break_' => Mutator\Operator\Break_::class,
        'Continue_' => Mutator\Operator\Continue_::class,
        'Throw_' => Mutator\Operator\Throw_::class,
        'Finally_' => Mutator\Operator\Finally_::class,
        'Coalesce' => Mutator\Operator\Coalesce::class,

        //Regex
        'PregQuote' => Mutator\Regex\PregQuote::class,
        'PregMatchMatches' => Mutator\Regex\PregMatchMatches::class,

        //Removal
        'FunctionCallRemoval' => Mutator\Removal\FunctionCallRemoval::class,
        'MethodCallRemoval' => Mutator\Removal\MethodCallRemoval::class,

        //Return Value
        'FloatNegation' => Mutator\ReturnValue\FloatNegation::class,
        'FunctionCall' => Mutator\ReturnValue\FunctionCall::class,
        'IntegerNegation' => Mutator\ReturnValue\IntegerNegation::class,
        'NewObject' => Mutator\ReturnValue\NewObject::class,
        'This' => Mutator\ReturnValue\This::class,

        //Sort
        'Spaceship' => Mutator\Sort\Spaceship::class,

        //Zero Iteration
        'Foreach_' => Mutator\ZeroIteration\Foreach_::class,
        'For_' => Mutator\ZeroIteration\For_::class,

        // Cast
        'CastArray' => Mutator\Cast\CastArray::class,
        'CastBool' => Mutator\Cast\CastBool::class,
        'CastFloat' => Mutator\Cast\CastFloat::class,
        'CastInt' => Mutator\Cast\CastInt::class,
        'CastObject' => Mutator\Cast\CastObject::class,
        'CastString' => Mutator\Cast\CastString::class,

        // Unwrap
        'UnwrapArrayFilter' => Mutator\Unwrap\UnwrapArrayFilter::class,
        'UnwrapArrayFlip' => Mutator\Unwrap\UnwrapArrayFlip::class,
        'UnwrapArrayMap' => Mutator\Unwrap\UnwrapArrayMap::class,
        'UnwrapArrayReduce' => Mutator\Unwrap\UnwrapArrayReduce::class,
        'UnwrapArrayReverse' => Mutator\Unwrap\UnwrapArrayReverse::class,
        'UnwrapStrRepeat' => Mutator\Unwrap\UnwrapStrRepeat::class,
    ];
}
