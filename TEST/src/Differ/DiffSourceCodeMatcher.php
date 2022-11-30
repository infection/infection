<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Differ;

use function _HumbugBox9658796bb9f0\Safe\preg_match;
use function str_contains;
final class DiffSourceCodeMatcher
{
    private const POSSIBLE_DELIMITERS = ['#', '%', ':', ';', '=', '?', '@', '^', '~'];
    public function matches(string $diff, string $sourceCodeRegex) : bool
    {
        $delimiter = $this->findDelimiter($sourceCodeRegex);
        return preg_match("{$delimiter}^-\\s*{$sourceCodeRegex}\${$delimiter}mu", $diff) === 1;
    }
    private function findDelimiter(string $sourceCodeRegex) : string
    {
        foreach (self::POSSIBLE_DELIMITERS as $possibleDelimiter) {
            if (!str_contains($sourceCodeRegex, $possibleDelimiter)) {
                return $possibleDelimiter;
            }
        }
        return '/';
    }
}
