<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node\Scalar;

use _HumbugBoxb47773b41c19\PhpParser\Error;
use _HumbugBoxb47773b41c19\PhpParser\Node\Scalar;
class String_ extends Scalar
{
    const KIND_SINGLE_QUOTED = 1;
    const KIND_DOUBLE_QUOTED = 2;
    const KIND_HEREDOC = 3;
    const KIND_NOWDOC = 4;
    public $value;
    protected static $replacements = ['\\' => '\\', '$' => '$', 'n' => "\n", 'r' => "\r", 't' => "\t", 'f' => "\f", 'v' => "\v", 'e' => "\x1b"];
    public function __construct(string $value, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->value = $value;
    }
    public function getSubNodeNames() : array
    {
        return ['value'];
    }
    public static function fromString(string $str, array $attributes = [], bool $parseUnicodeEscape = \true) : self
    {
        $attributes['kind'] = $str[0] === "'" || $str[1] === "'" && ($str[0] === 'b' || $str[0] === 'B') ? Scalar\String_::KIND_SINGLE_QUOTED : Scalar\String_::KIND_DOUBLE_QUOTED;
        $attributes['rawValue'] = $str;
        $string = self::parse($str, $parseUnicodeEscape);
        return new self($string, $attributes);
    }
    public static function parse(string $str, bool $parseUnicodeEscape = \true) : string
    {
        $bLength = 0;
        if ('b' === $str[0] || 'B' === $str[0]) {
            $bLength = 1;
        }
        if ('\'' === $str[$bLength]) {
            return \str_replace(['\\\\', '\\\''], ['\\', '\''], \substr($str, $bLength + 1, -1));
        } else {
            return self::parseEscapeSequences(\substr($str, $bLength + 1, -1), '"', $parseUnicodeEscape);
        }
    }
    public static function parseEscapeSequences(string $str, $quote, bool $parseUnicodeEscape = \true) : string
    {
        if (null !== $quote) {
            $str = \str_replace('\\' . $quote, $quote, $str);
        }
        $extra = '';
        if ($parseUnicodeEscape) {
            $extra = '|u\\{([0-9a-fA-F]+)\\}';
        }
        return \preg_replace_callback('~\\\\([\\\\$nrtfve]|[xX][0-9a-fA-F]{1,2}|[0-7]{1,3}' . $extra . ')~', function ($matches) {
            $str = $matches[1];
            if (isset(self::$replacements[$str])) {
                return self::$replacements[$str];
            } elseif ('x' === $str[0] || 'X' === $str[0]) {
                return \chr(\hexdec(\substr($str, 1)));
            } elseif ('u' === $str[0]) {
                return self::codePointToUtf8(\hexdec($matches[2]));
            } else {
                return \chr(\octdec($str));
            }
        }, $str);
    }
    private static function codePointToUtf8(int $num) : string
    {
        if ($num <= 0x7f) {
            return \chr($num);
        }
        if ($num <= 0x7ff) {
            return \chr(($num >> 6) + 0xc0) . \chr(($num & 0x3f) + 0x80);
        }
        if ($num <= 0xffff) {
            return \chr(($num >> 12) + 0xe0) . \chr(($num >> 6 & 0x3f) + 0x80) . \chr(($num & 0x3f) + 0x80);
        }
        if ($num <= 0x1fffff) {
            return \chr(($num >> 18) + 0xf0) . \chr(($num >> 12 & 0x3f) + 0x80) . \chr(($num >> 6 & 0x3f) + 0x80) . \chr(($num & 0x3f) + 0x80);
        }
        throw new Error('Invalid UTF-8 codepoint escape sequence: Codepoint too large');
    }
    public function getType() : string
    {
        return 'Scalar_String';
    }
}
