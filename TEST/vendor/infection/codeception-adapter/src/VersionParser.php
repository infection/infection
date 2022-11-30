<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Codeception;

use InvalidArgumentException;
use function preg_match;
class VersionParser
{
    private const VERSION_REGEX = '/(?<version>[0-9]+\\.[0-9]+\\.?[0-9]*)(?<prerelease>-[0-9a-zA-Z.]+)?(?<build>\\+[0-9a-zA-Z.]+)?/';
    public function parse(string $content) : string
    {
        $matches = [];
        $matched = preg_match(self::VERSION_REGEX, $content, $matches) > 0;
        if (!$matched) {
            throw new InvalidArgumentException('Parameter does not contain a valid SemVer (sub)string.');
        }
        return $matches[0];
    }
}
