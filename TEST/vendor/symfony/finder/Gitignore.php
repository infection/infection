<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Finder;

class Gitignore
{
    public static function toRegex(string $gitignoreFileContent) : string
    {
        return self::buildRegex($gitignoreFileContent, \false);
    }
    public static function toRegexMatchingNegatedPatterns(string $gitignoreFileContent) : string
    {
        return self::buildRegex($gitignoreFileContent, \true);
    }
    private static function buildRegex(string $gitignoreFileContent, bool $inverted) : string
    {
        $gitignoreFileContent = \preg_replace('~(?<!\\\\)#[^\\n\\r]*~', '', $gitignoreFileContent);
        $gitignoreLines = \preg_split('~\\r\\n?|\\n~', $gitignoreFileContent);
        $res = self::lineToRegex('');
        foreach ($gitignoreLines as $line) {
            $line = \preg_replace('~(?<!\\\\)[ \\t]+$~', '', $line);
            if ('!' === \substr($line, 0, 1)) {
                $line = \substr($line, 1);
                $isNegative = \true;
            } else {
                $isNegative = \false;
            }
            if ('' !== $line) {
                if ($isNegative xor $inverted) {
                    $res = '(?!' . self::lineToRegex($line) . '$)' . $res;
                } else {
                    $res = '(?:' . $res . '|' . self::lineToRegex($line) . ')';
                }
            }
        }
        return '~^(?:' . $res . ')~s';
    }
    private static function lineToRegex(string $gitignoreLine) : string
    {
        if ('' === $gitignoreLine) {
            return '$f';
        }
        $slashPos = \strpos($gitignoreLine, '/');
        if (\false !== $slashPos && \strlen($gitignoreLine) - 1 !== $slashPos) {
            if (0 === $slashPos) {
                $gitignoreLine = \substr($gitignoreLine, 1);
            }
            $isAbsolute = \true;
        } else {
            $isAbsolute = \false;
        }
        $regex = \preg_quote(\str_replace('\\', '', $gitignoreLine), '~');
        $regex = \preg_replace_callback('~\\\\\\[((?:\\\\!)?)([^\\[\\]]*)\\\\\\]~', function (array $matches) : string {
            return '[' . ('' !== $matches[1] ? '^' : '') . \str_replace('\\-', '-', $matches[2]) . ']';
        }, $regex);
        $regex = \preg_replace('~(?:(?:\\\\\\*){2,}(/?))+~', '(?:(?:(?!//).(?<!//))+$1)?', $regex);
        $regex = \preg_replace('~\\\\\\*~', '[^/]*', $regex);
        $regex = \preg_replace('~\\\\\\?~', '[^/]', $regex);
        return ($isAbsolute ? '' : '(?:[^/]+/)*') . $regex . (!\str_ends_with($gitignoreLine, '/') ? '(?:$|/)' : '');
    }
}
