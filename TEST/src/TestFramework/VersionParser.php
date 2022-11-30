<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework;

use function _HumbugBox9658796bb9f0\Safe\preg_match;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function str_replace;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class VersionParser
{
    private const VERSION_REGEX = '/(?<version>\\d+\\.\\d+\\.?\\d*)(?<prerelease>-[0-9a-zA-Z.]+)?(?<build>\\+[0-9a-zA-Z.]+)?/';
    public function parse(string $content) : string
    {
        $matches = [];
        $matched = preg_match(self::VERSION_REGEX, $content, $matches);
        Assert::notSame($matched, 0, sprintf('Expected "%s" to be contain a valid SemVer (sub)string value.', str_replace('%', '%%', $content)));
        return $matches[0];
    }
}
