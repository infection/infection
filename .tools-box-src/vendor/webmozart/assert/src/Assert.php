<?php

namespace _HumbugBoxb47773b41c19\Webmozart\Assert;

use ArrayAccess;
use BadMethodCallException;
use Closure;
use Countable;
use DateTime;
use DateTimeImmutable;
use Exception;
use ResourceBundle;
use SimpleXMLElement;
use Throwable;
use Traversable;
class Assert
{
    use Mixin;
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function string($value, $message = '')
    {
        if (!\is_string($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a string. Got: %s', static::typeToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function stringNotEmpty($value, $message = '')
    {
        static::string($value, $message);
        static::notEq($value, '', $message);
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function integer($value, $message = '')
    {
        if (!\is_int($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an integer. Got: %s', static::typeToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function integerish($value, $message = '')
    {
        if (!\is_numeric($value) || $value != (int) $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an integerish value. Got: %s', static::typeToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function positiveInteger($value, $message = '')
    {
        if (!(\is_int($value) && $value > 0)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a positive integer. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function float($value, $message = '')
    {
        if (!\is_float($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a float. Got: %s', static::typeToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function numeric($value, $message = '')
    {
        if (!\is_numeric($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a numeric. Got: %s', static::typeToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function natural($value, $message = '')
    {
        if (!\is_int($value) || $value < 0) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a non-negative integer. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function boolean($value, $message = '')
    {
        if (!\is_bool($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a boolean. Got: %s', static::typeToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function scalar($value, $message = '')
    {
        if (!\is_scalar($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a scalar. Got: %s', static::typeToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function object($value, $message = '')
    {
        if (!\is_object($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an object. Got: %s', static::typeToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function resource($value, $type = null, $message = '')
    {
        if (!\is_resource($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a resource. Got: %s', static::typeToString($value)));
        }
        if ($type && $type !== \get_resource_type($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a resource of type %2$s. Got: %s', static::typeToString($value), $type));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function isCallable($value, $message = '')
    {
        if (!\is_callable($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a callable. Got: %s', static::typeToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function isArray($value, $message = '')
    {
        if (!\is_array($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an array. Got: %s', static::typeToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function isTraversable($value, $message = '')
    {
        @\trigger_error(\sprintf('The "%s" assertion is deprecated. You should stop using it, as it will soon be removed in 2.0 version. Use "isIterable" or "isInstanceOf" instead.', __METHOD__), \E_USER_DEPRECATED);
        if (!\is_array($value) && !$value instanceof Traversable) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a traversable. Got: %s', static::typeToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function isArrayAccessible($value, $message = '')
    {
        if (!\is_array($value) && !$value instanceof ArrayAccess) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an array accessible. Got: %s', static::typeToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function isCountable($value, $message = '')
    {
        if (!\is_array($value) && !$value instanceof Countable && !$value instanceof ResourceBundle && !$value instanceof SimpleXMLElement) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a countable. Got: %s', static::typeToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function isIterable($value, $message = '')
    {
        if (!\is_array($value) && !$value instanceof Traversable) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an iterable. Got: %s', static::typeToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function isInstanceOf($value, $class, $message = '')
    {
        if (!$value instanceof $class) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an instance of %2$s. Got: %s', static::typeToString($value), $class));
        }
    }
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function notInstanceOf($value, $class, $message = '')
    {
        if ($value instanceof $class) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an instance other than %2$s. Got: %s', static::typeToString($value), $class));
        }
    }
    /**
    @psalm-pure
    @psalm-param
    */
    public static function isInstanceOfAny($value, array $classes, $message = '')
    {
        foreach ($classes as $class) {
            if ($value instanceof $class) {
                return;
            }
        }
        static::reportInvalidArgument(\sprintf($message ?: 'Expected an instance of any of %2$s. Got: %s', static::typeToString($value), \implode(', ', \array_map(array(static::class, 'valueToString'), $classes))));
    }
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function isAOf($value, $class, $message = '')
    {
        static::string($class, 'Expected class as a string. Got: %s');
        if (!\is_a($value, $class, \is_string($value))) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an instance of this class or to this class among its parents "%2$s". Got: %s', static::valueToString($value), $class));
        }
    }
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    @psalm-assert
    */
    public static function isNotA($value, $class, $message = '')
    {
        static::string($class, 'Expected class as a string. Got: %s');
        if (\is_a($value, $class, \is_string($value))) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an instance of this class or to this class among its parents other than "%2$s". Got: %s', static::valueToString($value), $class));
        }
    }
    /**
    @psalm-pure
    @psalm-param
    */
    public static function isAnyOf($value, array $classes, $message = '')
    {
        foreach ($classes as $class) {
            static::string($class, 'Expected class as a string. Got: %s');
            if (\is_a($value, $class, \is_string($value))) {
                return;
            }
        }
        static::reportInvalidArgument(\sprintf($message ?: 'Expected an instance of any of this classes or any of those classes among their parents "%2$s". Got: %s', static::valueToString($value), \implode(', ', $classes)));
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function isEmpty($value, $message = '')
    {
        if (!empty($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an empty value. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function notEmpty($value, $message = '')
    {
        if (empty($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a non-empty value. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function null($value, $message = '')
    {
        if (null !== $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected null. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function notNull($value, $message = '')
    {
        if (null === $value) {
            static::reportInvalidArgument($message ?: 'Expected a value other than null.');
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function true($value, $message = '')
    {
        if (\true !== $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be true. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function false($value, $message = '')
    {
        if (\false !== $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be false. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function notFalse($value, $message = '')
    {
        if (\false === $value) {
            static::reportInvalidArgument($message ?: 'Expected a value other than false.');
        }
    }
    public static function ip($value, $message = '')
    {
        if (\false === \filter_var($value, \FILTER_VALIDATE_IP)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be an IP. Got: %s', static::valueToString($value)));
        }
    }
    public static function ipv4($value, $message = '')
    {
        if (\false === \filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be an IPv4. Got: %s', static::valueToString($value)));
        }
    }
    public static function ipv6($value, $message = '')
    {
        if (\false === \filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be an IPv6. Got: %s', static::valueToString($value)));
        }
    }
    public static function email($value, $message = '')
    {
        if (\false === \filter_var($value, \FILTER_VALIDATE_EMAIL)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be a valid e-mail address. Got: %s', static::valueToString($value)));
        }
    }
    public static function uniqueValues(array $values, $message = '')
    {
        $allValues = \count($values);
        $uniqueValues = \count(\array_unique($values));
        if ($allValues !== $uniqueValues) {
            $difference = $allValues - $uniqueValues;
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an array of unique values, but %s of them %s duplicated', $difference, 1 === $difference ? 'is' : 'are'));
        }
    }
    public static function eq($value, $expect, $message = '')
    {
        if ($expect != $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value equal to %2$s. Got: %s', static::valueToString($value), static::valueToString($expect)));
        }
    }
    public static function notEq($value, $expect, $message = '')
    {
        if ($expect == $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a different value than %s.', static::valueToString($expect)));
        }
    }
    /**
    @psalm-pure
    */
    public static function same($value, $expect, $message = '')
    {
        if ($expect !== $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value identical to %2$s. Got: %s', static::valueToString($value), static::valueToString($expect)));
        }
    }
    /**
    @psalm-pure
    */
    public static function notSame($value, $expect, $message = '')
    {
        if ($expect === $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value not identical to %s.', static::valueToString($expect)));
        }
    }
    /**
    @psalm-pure
    */
    public static function greaterThan($value, $limit, $message = '')
    {
        if ($value <= $limit) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value greater than %2$s. Got: %s', static::valueToString($value), static::valueToString($limit)));
        }
    }
    /**
    @psalm-pure
    */
    public static function greaterThanEq($value, $limit, $message = '')
    {
        if ($value < $limit) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value greater than or equal to %2$s. Got: %s', static::valueToString($value), static::valueToString($limit)));
        }
    }
    /**
    @psalm-pure
    */
    public static function lessThan($value, $limit, $message = '')
    {
        if ($value >= $limit) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value less than %2$s. Got: %s', static::valueToString($value), static::valueToString($limit)));
        }
    }
    /**
    @psalm-pure
    */
    public static function lessThanEq($value, $limit, $message = '')
    {
        if ($value > $limit) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value less than or equal to %2$s. Got: %s', static::valueToString($value), static::valueToString($limit)));
        }
    }
    /**
    @psalm-pure
    */
    public static function range($value, $min, $max, $message = '')
    {
        if ($value < $min || $value > $max) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value between %2$s and %3$s. Got: %s', static::valueToString($value), static::valueToString($min), static::valueToString($max)));
        }
    }
    /**
    @psalm-pure
    */
    public static function oneOf($value, array $values, $message = '')
    {
        static::inArray($value, $values, $message);
    }
    /**
    @psalm-pure
    */
    public static function inArray($value, array $values, $message = '')
    {
        if (!\in_array($value, $values, \true)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected one of: %2$s. Got: %s', static::valueToString($value), \implode(', ', \array_map(array(static::class, 'valueToString'), $values))));
        }
    }
    /**
    @psalm-pure
    */
    public static function contains($value, $subString, $message = '')
    {
        if (\false === \strpos($value, $subString)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain %2$s. Got: %s', static::valueToString($value), static::valueToString($subString)));
        }
    }
    /**
    @psalm-pure
    */
    public static function notContains($value, $subString, $message = '')
    {
        if (\false !== \strpos($value, $subString)) {
            static::reportInvalidArgument(\sprintf($message ?: '%2$s was not expected to be contained in a value. Got: %s', static::valueToString($value), static::valueToString($subString)));
        }
    }
    /**
    @psalm-pure
    */
    public static function notWhitespaceOnly($value, $message = '')
    {
        if (\preg_match('/^\\s*$/', $value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a non-whitespace string. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    */
    public static function startsWith($value, $prefix, $message = '')
    {
        if (0 !== \strpos($value, $prefix)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to start with %2$s. Got: %s', static::valueToString($value), static::valueToString($prefix)));
        }
    }
    /**
    @psalm-pure
    */
    public static function notStartsWith($value, $prefix, $message = '')
    {
        if (0 === \strpos($value, $prefix)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value not to start with %2$s. Got: %s', static::valueToString($value), static::valueToString($prefix)));
        }
    }
    /**
    @psalm-pure
    */
    public static function startsWithLetter($value, $message = '')
    {
        static::string($value);
        $valid = isset($value[0]);
        if ($valid) {
            $locale = \setlocale(\LC_CTYPE, 0);
            \setlocale(\LC_CTYPE, 'C');
            $valid = \ctype_alpha($value[0]);
            \setlocale(\LC_CTYPE, $locale);
        }
        if (!$valid) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to start with a letter. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    */
    public static function endsWith($value, $suffix, $message = '')
    {
        if ($suffix !== \substr($value, -\strlen($suffix))) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to end with %2$s. Got: %s', static::valueToString($value), static::valueToString($suffix)));
        }
    }
    /**
    @psalm-pure
    */
    public static function notEndsWith($value, $suffix, $message = '')
    {
        if ($suffix === \substr($value, -\strlen($suffix))) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value not to end with %2$s. Got: %s', static::valueToString($value), static::valueToString($suffix)));
        }
    }
    /**
    @psalm-pure
    */
    public static function regex($value, $pattern, $message = '')
    {
        if (!\preg_match($pattern, $value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'The value %s does not match the expected pattern.', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    */
    public static function notRegex($value, $pattern, $message = '')
    {
        if (\preg_match($pattern, $value, $matches, \PREG_OFFSET_CAPTURE)) {
            static::reportInvalidArgument(\sprintf($message ?: 'The value %s matches the pattern %s (at offset %d).', static::valueToString($value), static::valueToString($pattern), $matches[0][1]));
        }
    }
    /**
    @psalm-pure
    */
    public static function unicodeLetters($value, $message = '')
    {
        static::string($value);
        if (!\preg_match('/^\\p{L}+$/u', $value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain only Unicode letters. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    */
    public static function alpha($value, $message = '')
    {
        static::string($value);
        $locale = \setlocale(\LC_CTYPE, 0);
        \setlocale(\LC_CTYPE, 'C');
        $valid = !\ctype_alpha($value);
        \setlocale(\LC_CTYPE, $locale);
        if ($valid) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain only letters. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    */
    public static function digits($value, $message = '')
    {
        $locale = \setlocale(\LC_CTYPE, 0);
        \setlocale(\LC_CTYPE, 'C');
        $valid = !\ctype_digit($value);
        \setlocale(\LC_CTYPE, $locale);
        if ($valid) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain digits only. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    */
    public static function alnum($value, $message = '')
    {
        $locale = \setlocale(\LC_CTYPE, 0);
        \setlocale(\LC_CTYPE, 'C');
        $valid = !\ctype_alnum($value);
        \setlocale(\LC_CTYPE, $locale);
        if ($valid) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain letters and digits only. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function lower($value, $message = '')
    {
        $locale = \setlocale(\LC_CTYPE, 0);
        \setlocale(\LC_CTYPE, 'C');
        $valid = !\ctype_lower($value);
        \setlocale(\LC_CTYPE, $locale);
        if ($valid) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain lowercase characters only. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function upper($value, $message = '')
    {
        $locale = \setlocale(\LC_CTYPE, 0);
        \setlocale(\LC_CTYPE, 'C');
        $valid = !\ctype_upper($value);
        \setlocale(\LC_CTYPE, $locale);
        if ($valid) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain uppercase characters only. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    */
    public static function length($value, $length, $message = '')
    {
        if ($length !== static::strlen($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain %2$s characters. Got: %s', static::valueToString($value), $length));
        }
    }
    /**
    @psalm-pure
    */
    public static function minLength($value, $min, $message = '')
    {
        if (static::strlen($value) < $min) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain at least %2$s characters. Got: %s', static::valueToString($value), $min));
        }
    }
    /**
    @psalm-pure
    */
    public static function maxLength($value, $max, $message = '')
    {
        if (static::strlen($value) > $max) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain at most %2$s characters. Got: %s', static::valueToString($value), $max));
        }
    }
    /**
    @psalm-pure
    */
    public static function lengthBetween($value, $min, $max, $message = '')
    {
        $length = static::strlen($value);
        if ($length < $min || $length > $max) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain between %2$s and %3$s characters. Got: %s', static::valueToString($value), $min, $max));
        }
    }
    public static function fileExists($value, $message = '')
    {
        static::string($value);
        if (!\file_exists($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'The file %s does not exist.', static::valueToString($value)));
        }
    }
    public static function file($value, $message = '')
    {
        static::fileExists($value, $message);
        if (!\is_file($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'The path %s is not a file.', static::valueToString($value)));
        }
    }
    public static function directory($value, $message = '')
    {
        static::fileExists($value, $message);
        if (!\is_dir($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'The path %s is no directory.', static::valueToString($value)));
        }
    }
    public static function readable($value, $message = '')
    {
        if (!\is_readable($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'The path %s is not readable.', static::valueToString($value)));
        }
    }
    public static function writable($value, $message = '')
    {
        if (!\is_writable($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'The path %s is not writable.', static::valueToString($value)));
        }
    }
    /**
    @psalm-assert
    */
    public static function classExists($value, $message = '')
    {
        if (!\class_exists($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an existing class name. Got: %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function subclassOf($value, $class, $message = '')
    {
        if (!\is_subclass_of($value, $class)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a sub-class of %2$s. Got: %s', static::valueToString($value), static::valueToString($class)));
        }
    }
    /**
    @psalm-assert
    */
    public static function interfaceExists($value, $message = '')
    {
        if (!\interface_exists($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an existing interface name. got %s', static::valueToString($value)));
        }
    }
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function implementsInterface($value, $interface, $message = '')
    {
        if (!\in_array($interface, \class_implements($value))) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an implementation of %2$s. Got: %s', static::valueToString($value), static::valueToString($interface)));
        }
    }
    /**
    @psalm-pure
    @psalm-param
    */
    public static function propertyExists($classOrObject, $property, $message = '')
    {
        if (!\property_exists($classOrObject, $property)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected the property %s to exist.', static::valueToString($property)));
        }
    }
    /**
    @psalm-pure
    @psalm-param
    */
    public static function propertyNotExists($classOrObject, $property, $message = '')
    {
        if (\property_exists($classOrObject, $property)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected the property %s to not exist.', static::valueToString($property)));
        }
    }
    /**
    @psalm-pure
    @psalm-param
    */
    public static function methodExists($classOrObject, $method, $message = '')
    {
        if (!(\is_string($classOrObject) || \is_object($classOrObject)) || !\method_exists($classOrObject, $method)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected the method %s to exist.', static::valueToString($method)));
        }
    }
    /**
    @psalm-pure
    @psalm-param
    */
    public static function methodNotExists($classOrObject, $method, $message = '')
    {
        if ((\is_string($classOrObject) || \is_object($classOrObject)) && \method_exists($classOrObject, $method)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected the method %s to not exist.', static::valueToString($method)));
        }
    }
    /**
    @psalm-pure
    */
    public static function keyExists($array, $key, $message = '')
    {
        if (!(isset($array[$key]) || \array_key_exists($key, $array))) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected the key %s to exist.', static::valueToString($key)));
        }
    }
    /**
    @psalm-pure
    */
    public static function keyNotExists($array, $key, $message = '')
    {
        if (isset($array[$key]) || \array_key_exists($key, $array)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected the key %s to not exist.', static::valueToString($key)));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function validArrayKey($value, $message = '')
    {
        if (!(\is_int($value) || \is_string($value))) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected string or integer. Got: %s', static::typeToString($value)));
        }
    }
    public static function count($array, $number, $message = '')
    {
        static::eq(\count($array), $number, \sprintf($message ?: 'Expected an array to contain %d elements. Got: %d.', $number, \count($array)));
    }
    public static function minCount($array, $min, $message = '')
    {
        if (\count($array) < $min) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an array to contain at least %2$d elements. Got: %d', \count($array), $min));
        }
    }
    public static function maxCount($array, $max, $message = '')
    {
        if (\count($array) > $max) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an array to contain at most %2$d elements. Got: %d', \count($array), $max));
        }
    }
    public static function countBetween($array, $min, $max, $message = '')
    {
        $count = \count($array);
        if ($count < $min || $count > $max) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an array to contain between %2$d and %3$d elements. Got: %d', $count, $min, $max));
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function isList($array, $message = '')
    {
        if (!\is_array($array)) {
            static::reportInvalidArgument($message ?: 'Expected list - non-associative array.');
        }
        if ($array === \array_values($array)) {
            return;
        }
        $nextKey = -1;
        foreach ($array as $k => $v) {
            if ($k !== ++$nextKey) {
                static::reportInvalidArgument($message ?: 'Expected list - non-associative array.');
            }
        }
    }
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function isNonEmptyList($array, $message = '')
    {
        static::isList($array, $message);
        static::notEmpty($array, $message);
    }
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function isMap($array, $message = '')
    {
        if (!\is_array($array) || \array_keys($array) !== \array_filter(\array_keys($array), '\\is_string')) {
            static::reportInvalidArgument($message ?: 'Expected map - associative array with string keys.');
        }
    }
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    @psalm-assert
    */
    public static function isNonEmptyMap($array, $message = '')
    {
        static::isMap($array, $message);
        static::notEmpty($array, $message);
    }
    /**
    @psalm-pure
    */
    public static function uuid($value, $message = '')
    {
        $value = \str_replace(array('urn:', 'uuid:', '{', '}'), '', $value);
        if ('00000000-0000-0000-0000-000000000000' === $value) {
            return;
        }
        if (!\preg_match('/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/', $value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Value %s is not a valid UUID.', static::valueToString($value)));
        }
    }
    /**
    @psalm-param
    */
    public static function throws(Closure $expression, $class = 'Exception', $message = '')
    {
        static::string($class);
        $actual = 'none';
        try {
            $expression();
        } catch (Exception $e) {
            $actual = \get_class($e);
            if ($e instanceof $class) {
                return;
            }
        } catch (Throwable $e) {
            $actual = \get_class($e);
            if ($e instanceof $class) {
                return;
            }
        }
        static::reportInvalidArgument($message ?: \sprintf('Expected to throw "%s", got "%s"', $class, $actual));
    }
    public static function __callStatic($name, $arguments)
    {
        if ('nullOr' === \substr($name, 0, 6)) {
            if (null !== $arguments[0]) {
                $method = \lcfirst(\substr($name, 6));
                \call_user_func_array(array(static::class, $method), $arguments);
            }
            return;
        }
        if ('all' === \substr($name, 0, 3)) {
            static::isIterable($arguments[0]);
            $method = \lcfirst(\substr($name, 3));
            $args = $arguments;
            foreach ($arguments[0] as $entry) {
                $args[0] = $entry;
                \call_user_func_array(array(static::class, $method), $args);
            }
            return;
        }
        throw new BadMethodCallException('No such method: ' . $name);
    }
    protected static function valueToString($value)
    {
        if (null === $value) {
            return 'null';
        }
        if (\true === $value) {
            return 'true';
        }
        if (\false === $value) {
            return 'false';
        }
        if (\is_array($value)) {
            return 'array';
        }
        if (\is_object($value)) {
            if (\method_exists($value, '__toString')) {
                return \get_class($value) . ': ' . self::valueToString($value->__toString());
            }
            if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
                return \get_class($value) . ': ' . self::valueToString($value->format('c'));
            }
            return \get_class($value);
        }
        if (\is_resource($value)) {
            return 'resource';
        }
        if (\is_string($value)) {
            return '"' . $value . '"';
        }
        return (string) $value;
    }
    protected static function typeToString($value)
    {
        return \is_object($value) ? \get_class($value) : \gettype($value);
    }
    protected static function strlen($value)
    {
        if (!\function_exists('mb_detect_encoding')) {
            return \strlen($value);
        }
        if (\false === ($encoding = \mb_detect_encoding($value))) {
            return \strlen($value);
        }
        return \mb_strlen($value, $encoding);
    }
    /**
    @psalm-pure
    @psalm-return
    */
    protected static function reportInvalidArgument($message)
    {
        throw new InvalidArgumentException($message);
    }
    private function __construct()
    {
    }
}
