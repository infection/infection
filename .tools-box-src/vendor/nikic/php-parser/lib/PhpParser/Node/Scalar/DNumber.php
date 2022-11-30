<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Scalar;

use _HumbugBoxb47773b41c19\PhpParser\Node\Scalar;
class DNumber extends Scalar
{
    public $value;
    public function __construct(float $value, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->value = $value;
    }
    public function getSubNodeNames() : array
    {
        return ['value'];
    }
    public static function fromString(string $str, array $attributes = []) : DNumber
    {
        $attributes['rawValue'] = $str;
        $float = self::parse($str);
        return new DNumber($float, $attributes);
    }
    public static function parse(string $str) : float
    {
        $str = \str_replace('_', '', $str);
        if ('0' === $str[0]) {
            if ('x' === $str[1] || 'X' === $str[1]) {
                return \hexdec($str);
            }
            if ('b' === $str[1] || 'B' === $str[1]) {
                return \bindec($str);
            }
            if (\false === \strpbrk($str, '.eE')) {
                return \octdec(\substr($str, 0, \strcspn($str, '89')));
            }
        }
        return (float) $str;
    }
    public function getType() : string
    {
        return 'Scalar_DNumber';
    }
}
