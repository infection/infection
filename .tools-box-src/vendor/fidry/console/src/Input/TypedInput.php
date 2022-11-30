<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Input;

use _HumbugBoxb47773b41c19\Fidry\Console\InputAssert;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NaturalRangeType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\StringChoiceType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\TypeFactory;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
use function sprintf;
/**
@psalm-import-type
@psalm-import-type
*/
final class TypedInput
{
    private $value;
    private string $label;
    private function __construct($value, string $label)
    {
        Assert::stringNotEmpty($label);
        $this->value = $value;
        $this->label = $label;
    }
    public static function fromArgument($argument, string $name) : self
    {
        InputAssert::assertIsValidArgumentType($argument, $name);
        return new self($argument, sprintf('the argument "%s"', $name));
    }
    public static function fromOption($option, string $name) : self
    {
        InputAssert::assertIsValidOptionType($option, $name);
        return new self($option, sprintf('the option "%s"', $name));
    }
    public function asStringChoice(array $choices, ?string $errorMessage = null) : string
    {
        $type = new StringChoiceType($choices);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    /**
    @psalm-suppress
    */
    public function asNaturalWithinRange(int $min, int $max, ?string $errorMessage = null) : int
    {
        $type = new NaturalRangeType($min, $max);
        if (null === $errorMessage) {
            /**
            @psalm-suppress */
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            /**
            @psalm-suppress */
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asRaw(?string $errorMessage = null)
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\RawType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asBoolean(?string $errorMessage = null) : bool
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\BooleanType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNullableBoolean(?string $errorMessage = null) : ?bool
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NullableType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\BooleanType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asBooleanList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\ListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\BooleanType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asBooleanNonEmptyList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NonEmptyListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\BooleanType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNatural(?string $errorMessage = null) : int
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NaturalType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNullableNatural(?string $errorMessage = null) : ?int
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NullableType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NaturalType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNaturalList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\ListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NaturalType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNaturalNonEmptyList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NonEmptyListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NaturalType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asPositiveInteger(?string $errorMessage = null) : int
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\PositiveIntegerType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNullablePositiveInteger(?string $errorMessage = null) : ?int
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NullableType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\PositiveIntegerType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asPositiveIntegerList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\ListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\PositiveIntegerType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asPositiveIntegerNonEmptyList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NonEmptyListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\PositiveIntegerType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asFloat(?string $errorMessage = null) : float
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\FloatType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNullableFloat(?string $errorMessage = null) : ?float
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NullableType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\FloatType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asFloatList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\ListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\FloatType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asFloatNonEmptyList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NonEmptyListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\FloatType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asString(?string $errorMessage = null) : string
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\StringType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNullableString(?string $errorMessage = null) : ?string
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NullableType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\StringType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asStringList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\ListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\StringType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asStringNonEmptyList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NonEmptyListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\StringType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNonEmptyString(?string $errorMessage = null) : string
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NonEmptyStringType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNullableNonEmptyString(?string $errorMessage = null) : ?string
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NullableType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NonEmptyStringType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNonEmptyStringList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\ListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NonEmptyStringType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNonEmptyStringNonEmptyList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NonEmptyListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NonEmptyStringType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asUntrimmedString(?string $errorMessage = null) : string
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\UntrimmedStringType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNullableUntrimmedString(?string $errorMessage = null) : ?string
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NullableType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\UntrimmedStringType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asUntrimmedStringList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\ListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\UntrimmedStringType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asUntrimmedStringNonEmptyList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NonEmptyListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\UntrimmedStringType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNullOrNonEmptyString(?string $errorMessage = null) : ?string
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NullOrNonEmptyStringType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNullOrNonEmptyStringList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\ListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NullOrNonEmptyStringType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
    public function asNullOrNonEmptyStringNonEmptyList(?string $errorMessage = null) : array
    {
        $type = TypeFactory::createTypeFromClassNames([\_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NonEmptyListType::class, \_HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NullOrNonEmptyStringType::class]);
        if (null === $errorMessage) {
            return $type->coerceValue($this->value, $this->label);
        }
        try {
            return $type->coerceValue($this->value, $this->label);
        } catch (InvalidInputValueType $coercingFailed) {
            throw InvalidInputValueType::withErrorMessage($coercingFailed, $errorMessage);
        }
    }
}
