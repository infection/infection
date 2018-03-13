<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
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
use Infection\Mutator\Arithmetic\ShiftLeft;
use Infection\Mutator\Arithmetic\ShiftRight;
use Infection\Mutator\Boolean\FalseValue;
use Infection\Mutator\Boolean\LogicalAnd;
use Infection\Mutator\Boolean\LogicalLowerAnd;
use Infection\Mutator\Boolean\LogicalLowerOr;
use Infection\Mutator\Boolean\LogicalNot;
use Infection\Mutator\Boolean\LogicalOr;
use Infection\Mutator\Boolean\TrueValue;
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
use Infection\Mutator\FunctionSignature\ProtectedVisibility;
use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\Mutator\Number\DecrementInteger;
use Infection\Mutator\Number\IncrementInteger;
use Infection\Mutator\Number\OneZeroFloat;
use Infection\Mutator\Number\OneZeroInteger;
use Infection\Mutator\Operator\Break_;
use Infection\Mutator\Operator\Continue_;
use Infection\Mutator\Operator\Throw_;
use Infection\Mutator\ReturnValue\FloatNegation;
use Infection\Mutator\ReturnValue\FunctionCall;
use Infection\Mutator\ReturnValue\IntegerNegation;
use Infection\Mutator\ReturnValue\NewObject;
use Infection\Mutator\ReturnValue\This;
use Infection\Mutator\Sort\Spaceship;
use Infection\Mutator\ZeroIteration\For_;
use Infection\Mutator\ZeroIteration\Foreach_;

class MutatorProfile
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
        ShiftLeft::class,
        ShiftRight::class,
    ];

    const BOOLEAN = [
        FalseValue::class,
        LogicalAnd::class,
        LogicalLowerAnd::class,
        LogicalLowerOr::class,
        LogicalNot::class,
        LogicalOr::class,
        TrueValue::class,
    ];

    const CONDITIONAL_BOUNDARY = [
        GreaterThan::class,
        GreaterThanOrEqualTo::class,
        LessThan::class,
        LessThanOrEqualTo::class,
    ];

    const CONDITIONAL_NEGOTIATION = [
        Equal::class,
        GreaterThanNegotiation::class,
        GreaterThanOrEqualToNegotiation::class,
        Identical::class,
        LessThanNegotiation::class,
        LessThanOrEqualToNegotiation::class,
        NotEqual::class,
        NotIdentical::class,
    ];

    const FUNCTION_SIGNATURE = [
        PublicVisibility::class,
        ProtectedVisibility::class,
    ];

    const NUMBER = [
        DecrementInteger::class,
        IncrementInteger::class,
        OneZeroInteger::class,
        OneZeroFloat::class,
    ];

    const OPERATOR = [
        Break_::class,
        Continue_::class,
        Throw_::class,
    ];

    const RETURN_VALUE = [
        FloatNegation::class,
        FunctionCall::class,
        IntegerNegation::class,
        NewObject::class,
        This::class,
    ];

    const SORT = [
        Spaceship::class,
    ];

    const ZERO_ITERATION = [
        Foreach_::class,
        For_::class
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
}
