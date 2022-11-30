<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Exception\PcreException;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
use function preg_last_error;
use function preg_split as php_preg_split;
abstract class Utils
{
    public static function pregSplit(string $pattern, string $subject, int $limit = -1, int $flags = 0) : array
    {
        $parts = php_preg_split($pattern, $subject, $limit, $flags);
        if ($parts === \false) {
            throw PcreException::createFromPhpError(preg_last_error());
        }
        Assert::allString($parts);
        return $parts;
    }
}
