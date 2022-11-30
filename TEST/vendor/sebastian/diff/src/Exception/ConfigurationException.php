<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\SebastianBergmann\Diff;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;
use Exception;
final class ConfigurationException extends InvalidArgumentException
{
    public function __construct(string $option, string $expected, $value, int $code = 0, Exception $previous = null)
    {
        parent::__construct(sprintf('Option "%s" must be %s, got "%s".', $option, $expected, is_object($value) ? get_class($value) : (null === $value ? '<null>' : gettype($value) . '#' . $value)), $code, $previous);
    }
}
