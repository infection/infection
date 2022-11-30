<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Differ;

use function array_map;
use function explode;
use function implode;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function str_starts_with;
class DiffColorizer
{
    public function colorize(string $diff) : string
    {
        $lines = array_map(static function (string $line) : string {
            if (str_starts_with($line, '-')) {
                return sprintf('<diff-del>%s</diff-del>', $line);
            }
            if (str_starts_with($line, '+')) {
                return sprintf('<diff-add>%s</diff-add>', $line);
            }
            return $line;
        }, explode("\n", $diff));
        return sprintf('<code>%s%s</code>', "\n", implode("\n", $lines));
    }
}
