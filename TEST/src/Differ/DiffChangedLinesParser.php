<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Differ;

use function array_map;
use function count;
use function explode;
use function _HumbugBox9658796bb9f0\Safe\preg_match;
use function _HumbugBox9658796bb9f0\Safe\preg_split;
use function _HumbugBox9658796bb9f0\Safe\realpath;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function str_starts_with;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class DiffChangedLinesParser
{
    private const MATCH_INDEX = 1;
    public function parse(string $unifiedGreppedDiff) : array
    {
        $lines = preg_split('/\\n|\\r\\n?/', $unifiedGreppedDiff);
        $filePath = null;
        $resultMap = [];
        foreach ($lines as $line) {
            if (str_starts_with($line, 'diff ')) {
                preg_match('/diff.*a\\/.*\\sb\\/(.*)/', $line, $matches);
                Assert::keyExists($matches, self::MATCH_INDEX, sprintf('Source file can not be found in the following diff line: "%s"', $line));
                $filePath = realpath($matches[self::MATCH_INDEX]);
            } elseif (str_starts_with($line, '@@ ')) {
                Assert::string($filePath, sprintf('Real path for file from diff can not be calculated. Diff: %s', $unifiedGreppedDiff));
                preg_match('/\\s\\+(.*)\\s@/', $line, $matches);
                Assert::keyExists($matches, self::MATCH_INDEX, sprintf('Added/modified lines can not be found in the following diff line: "%s"', $line));
                $linesText = $matches[self::MATCH_INDEX];
                $lineParts = array_map('\\intval', explode(',', $linesText));
                Assert::minCount($lineParts, 1);
                $startLine = $lineParts[0];
                $endLine = count($lineParts) > 1 ? $lineParts[0] + $lineParts[1] - 1 : $startLine;
                $resultMap[$filePath][] = new ChangedLinesRange($startLine, $endLine);
            }
        }
        return $resultMap;
    }
}
