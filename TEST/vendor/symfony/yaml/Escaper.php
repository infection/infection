<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Yaml;

class Escaper
{
    public const REGEX_CHARACTER_TO_ESCAPE = "[\\x00-\\x1f]||| | | ";
    private const ESCAPEES = ['\\', '\\\\', '\\"', '"', "\x00", "\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07", "\x08", "\t", "\n", "\v", "\f", "\r", "\x0e", "\x0f", "\x10", "\x11", "\x12", "\x13", "\x14", "\x15", "\x16", "\x17", "\x18", "\x19", "\x1a", "\x1b", "\x1c", "\x1d", "\x1e", "\x1f", "", "", " ", " ", " "];
    private const ESCAPED = ['\\\\', '\\"', '\\\\', '\\"', '\\0', '\\x01', '\\x02', '\\x03', '\\x04', '\\x05', '\\x06', '\\a', '\\b', '\\t', '\\n', '\\v', '\\f', '\\r', '\\x0e', '\\x0f', '\\x10', '\\x11', '\\x12', '\\x13', '\\x14', '\\x15', '\\x16', '\\x17', '\\x18', '\\x19', '\\x1a', '\\e', '\\x1c', '\\x1d', '\\x1e', '\\x1f', '\\x7f', '\\N', '\\_', '\\L', '\\P'];
    public static function requiresDoubleQuoting(string $value) : bool
    {
        return 0 < \preg_match('/' . self::REGEX_CHARACTER_TO_ESCAPE . '/u', $value);
    }
    public static function escapeWithDoubleQuotes(string $value) : string
    {
        return \sprintf('"%s"', \str_replace(self::ESCAPEES, self::ESCAPED, $value));
    }
    public static function requiresSingleQuoting(string $value) : bool
    {
        if (\in_array(\strtolower($value), ['null', '~', 'true', 'false', 'y', 'n', 'yes', 'no', 'on', 'off'])) {
            return \true;
        }
        return 0 < \preg_match('/[ \\s \' " \\: \\{ \\} \\[ \\] , & \\* \\# \\?] | \\A[ \\- ? | < > = ! % @ ` \\p{Zs}]/xu', $value);
    }
    public static function escapeWithSingleQuotes(string $value) : string
    {
        return \sprintf("'%s'", \str_replace('\'', '\'\'', $value));
    }
}
