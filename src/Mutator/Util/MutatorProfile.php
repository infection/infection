<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Util;

use Infection\Mutator;

final class MutatorProfile
{
    const MUTATOR_PROFILE_LIST = [
        //Per category
        '@arithmetic' => self::ARITHMETIC,
        '@boolean' => self::BOOLEAN,
        '@conditional_boundary' => self::CONDITIONAL_BOUNDARY,
        '@conditional_negotiation' => self::CONDITIONAL_NEGOTIATION,
        '@function_signature' => self::FUNCTION_SIGNATURE,
        '@number' => self::NUMBER,
        '@operator' => self::OPERATOR,
        '@return_value' => self::RETURN_VALUE,
        '@sort' => self::SORT,
        '@zero_iteration' => self::ZERO_ITERATION,

        //Special Profiles
        '@default' => self::DEFAULT,
    ];

    const ARITHMETIC = [
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
    ];

    const BOOLEAN = [
        Mutator\Boolean\ArrayItem::class,
        Mutator\Boolean\FalseValue::class,
        Mutator\Boolean\IdenticalEqual::class,
        Mutator\Boolean\LogicalAnd::class,
        Mutator\Boolean\LogicalLowerAnd::class,
        Mutator\Boolean\LogicalLowerOr::class,
        Mutator\Boolean\LogicalNot::class,
        Mutator\Boolean\LogicalOr::class,
        Mutator\Boolean\NotIdenticalNotEqual::class,
        Mutator\Boolean\TrueValue::class,
        Mutator\Boolean\Yield_::class,
    ];

    const CONDITIONAL_BOUNDARY = [
        Mutator\ConditionalBoundary\GreaterThan::class,
        Mutator\ConditionalBoundary\GreaterThanOrEqualTo::class,
        Mutator\ConditionalBoundary\LessThan::class,
        Mutator\ConditionalBoundary\LessThanOrEqualTo::class,
    ];

    const CONDITIONAL_NEGOTIATION = [
        Mutator\ConditionalNegotiation\Equal::class,
        Mutator\ConditionalNegotiation\GreaterThanNegotiation::class,
        Mutator\ConditionalNegotiation\GreaterThanOrEqualToNegotiation::class,
        Mutator\ConditionalNegotiation\Identical::class,
        Mutator\ConditionalNegotiation\LessThanNegotiation::class,
        Mutator\ConditionalNegotiation\LessThanOrEqualToNegotiation::class,
        Mutator\ConditionalNegotiation\NotEqual::class,
        Mutator\ConditionalNegotiation\NotIdentical::class,
    ];

    const FUNCTION_SIGNATURE = [
        Mutator\FunctionSignature\PublicVisibility::class,
        Mutator\FunctionSignature\ProtectedVisibility::class,
    ];

    const NUMBER = [
        Mutator\Number\DecrementInteger::class,
        Mutator\Number\IncrementInteger::class,
        Mutator\Number\OneZeroInteger::class,
        Mutator\Number\OneZeroFloat::class,
    ];

    const OPERATOR = [
        Mutator\Operator\Break_::class,
        Mutator\Operator\Continue_::class,
        Mutator\Operator\Throw_::class,
    ];

    const RETURN_VALUE = [
        Mutator\ReturnValue\FloatNegation::class,
        Mutator\ReturnValue\FunctionCall::class,
        Mutator\ReturnValue\IntegerNegation::class,
        Mutator\ReturnValue\NewObject::class,
        Mutator\ReturnValue\This::class,
    ];

    const SORT = [
        Mutator\Sort\Spaceship::class,
    ];

    const ZERO_ITERATION = [
        Mutator\ZeroIteration\Foreach_::class,
        Mutator\ZeroIteration\For_::class,
    ];

    const DEFAULT = [
        '@arithmetic',
        '@boolean',
        '@conditional_boundary',
        '@conditional_negotiation',
        '@function_signature',
        '@number',
        '@operator',
        '@return_value',
        '@sort',
        '@zero_iteration',
    ];

    const FULL_MUTATOR_LIST = [
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

        //Boolean
        'ArrayItem' => Mutator\Boolean\ArrayItem::class,
        'FalseValue' => Mutator\Boolean\FalseValue::class,
        'IdenticalEqual' => Mutator\Boolean\IdenticalEqual::class,
        'LogicalAnd' => Mutator\Boolean\LogicalAnd::class,
        'LogicalLowerAnd' => Mutator\Boolean\LogicalLowerAnd::class,
        'LogicalLowerOr' => Mutator\Boolean\LogicalLowerOr::class,
        'LogicalNot' => Mutator\Boolean\LogicalNot::class,
        'LogicalOr' => Mutator\Boolean\LogicalOr::class,
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
    ];
}
