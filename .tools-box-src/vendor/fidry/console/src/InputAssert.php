<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console;

use _HumbugBoxb47773b41c19\Fidry\Console\Input\InvalidInputValueType;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
use _HumbugBoxb47773b41c19\Webmozart\Assert\InvalidArgumentException as AssertInvalidArgumentException;
use function array_is_list;
use function get_debug_type;
use function is_array;
use function is_bool;
use function is_string;
use function sprintf;
use function var_export;
/**
@psalm-type
@psalm-type
*/
final class InputAssert
{
    private function __construct()
    {
    }
    /**
    @psalm-assert
    */
    public static function assertIsValidArgumentType($argument, string $name) : void
    {
        if (null === $argument || is_string($argument)) {
            return;
        }
        if (!is_array($argument) || !array_is_list($argument)) {
            throw new InvalidInputValueType(sprintf('Expected an argument value type to be "null|string|list<string>". Got "%s" for the argument "%s".', get_debug_type($argument), $name));
        }
        foreach ($argument as $item) {
            self::assertIsValidArgumentType($item, $name);
        }
    }
    /**
    @psalm-assert
    */
    public static function assertIsValidOptionType($option, string $name) : void
    {
        if (null === $option || is_bool($option) || is_string($option)) {
            return;
        }
        if (!is_array($option) || !array_is_list($option)) {
            throw new InvalidInputValueType(sprintf('Expected an option value type to be "null|bool|string|list<string>". Got "%s" for the option "%s".', get_debug_type($option), $name));
        }
        foreach ($option as $item) {
            self::assertIsValidOptionType($item, $name);
        }
    }
    /**
    @psalm-assert
    */
    public static function assertIsScalar($value, string $label) : void
    {
        self::castThrowException(static function () use($value) : void {
            if (null === $value) {
                return;
            }
            Assert::scalar($value, sprintf('Expected a null or scalar value. Got the value: "%s"', self::castType($value)));
        }, $label);
    }
    /**
    @psalm-assert
    */
    public static function assertIsList($value, string $label) : void
    {
        self::castThrowException(static function () use($value) : void {
            Assert::isArray($value, sprintf('Cannot cast a non-array input argument into an array. Got "%s"', self::castType($value)));
            /**
            @psalm-suppress */
            Assert::isList($value, sprintf('Expected array to be a list. Got "%s"', self::castType($value)));
        }, $label);
    }
    /**
    @psalm-assert
    */
    public static function numericString($value, string $label) : void
    {
        self::castThrowException(static function () use($value, $label) : void {
            self::assertIsScalar($value, $label);
            Assert::string($value, sprintf('Expected a numeric string. Got "%s"', self::castType($value)));
            Assert::numeric($value, sprintf('Expected a numeric string. Got "%s"', self::castType($value)));
        }, $label);
    }
    /**
    @psalm-assert
    */
    public static function integerString($value, string $label) : void
    {
        self::castThrowException(static function () use($value, $label) : void {
            self::assertIsScalar($value, $label);
            Assert::string($value, sprintf('Expected an integer string. Got "%s"', self::castType($value)));
            Assert::digits($value, sprintf('Expected an integer string. Got "%s"', self::castType($value)));
        }, $label);
    }
    /**
    @psalm-assert
    */
    public static function string($value, string $label) : void
    {
        self::castThrowException(static function () use($value, $label) : void {
            self::assertIsScalar($value, $label);
            Assert::string($value, sprintf('Expected a string. Got "%s"', self::castType($value)));
        }, $label);
    }
    /**
     * @param callable(): void $callable
     * @param non-empty-string $label
     */
    public static function castThrowException(callable $callable, string $label) : void
    {
        try {
            $callable();
        } catch (AssertInvalidArgumentException $exception) {
            throw InvalidInputValueType::fromAssert($exception, $label);
        }
    }
    private static function castType($value) : string
    {
        return var_export($value, \true);
    }
}
