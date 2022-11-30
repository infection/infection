<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\SebastianBergmann\Diff\Output;

use function array_splice;
use function count;
use function fclose;
use function fopen;
use function fwrite;
use function max;
use function min;
use function stream_get_contents;
use function strlen;
use function substr;
use _HumbugBox9658796bb9f0\SebastianBergmann\Diff\Differ;
final class UnifiedDiffOutputBuilder extends AbstractChunkOutputBuilder
{
    private $collapseRanges = \true;
    private $commonLineThreshold = 6;
    private $contextLines = 3;
    private $header;
    private $addLineNumbers;
    public function __construct(string $header = "--- Original\n+++ New\n", bool $addLineNumbers = \false)
    {
        $this->header = $header;
        $this->addLineNumbers = $addLineNumbers;
    }
    public function getDiff(array $diff) : string
    {
        $buffer = fopen('php://memory', 'r+b');
        if ('' !== $this->header) {
            fwrite($buffer, $this->header);
            if ("\n" !== substr($this->header, -1, 1)) {
                fwrite($buffer, "\n");
            }
        }
        if (0 !== count($diff)) {
            $this->writeDiffHunks($buffer, $diff);
        }
        $diff = stream_get_contents($buffer, -1, 0);
        fclose($buffer);
        $last = substr($diff, -1);
        return 0 !== strlen($diff) && "\n" !== $last && "\r" !== $last ? $diff . "\n" : $diff;
    }
    private function writeDiffHunks($output, array $diff) : void
    {
        $upperLimit = count($diff);
        if (0 === $diff[$upperLimit - 1][1]) {
            $lc = substr($diff[$upperLimit - 1][0], -1);
            if ("\n" !== $lc) {
                array_splice($diff, $upperLimit, 0, [["\n\\ No newline at end of file\n", Differ::NO_LINE_END_EOF_WARNING]]);
            }
        } else {
            $toFind = [1 => \true, 2 => \true];
            for ($i = $upperLimit - 1; $i >= 0; --$i) {
                if (isset($toFind[$diff[$i][1]])) {
                    unset($toFind[$diff[$i][1]]);
                    $lc = substr($diff[$i][0], -1);
                    if ("\n" !== $lc) {
                        array_splice($diff, $i + 1, 0, [["\n\\ No newline at end of file\n", Differ::NO_LINE_END_EOF_WARNING]]);
                    }
                    if (!count($toFind)) {
                        break;
                    }
                }
            }
        }
        $cutOff = max($this->commonLineThreshold, $this->contextLines);
        $hunkCapture = \false;
        $sameCount = $toRange = $fromRange = 0;
        $toStart = $fromStart = 1;
        $i = 0;
        foreach ($diff as $i => $entry) {
            if (0 === $entry[1]) {
                if (\false === $hunkCapture) {
                    ++$fromStart;
                    ++$toStart;
                    continue;
                }
                ++$sameCount;
                ++$toRange;
                ++$fromRange;
                if ($sameCount === $cutOff) {
                    $contextStartOffset = $hunkCapture - $this->contextLines < 0 ? $hunkCapture : $this->contextLines;
                    $this->writeHunk($diff, $hunkCapture - $contextStartOffset, $i - $cutOff + $this->contextLines + 1, $fromStart - $contextStartOffset, $fromRange - $cutOff + $contextStartOffset + $this->contextLines, $toStart - $contextStartOffset, $toRange - $cutOff + $contextStartOffset + $this->contextLines, $output);
                    $fromStart += $fromRange;
                    $toStart += $toRange;
                    $hunkCapture = \false;
                    $sameCount = $toRange = $fromRange = 0;
                }
                continue;
            }
            $sameCount = 0;
            if ($entry[1] === Differ::NO_LINE_END_EOF_WARNING) {
                continue;
            }
            if (\false === $hunkCapture) {
                $hunkCapture = $i;
            }
            if (Differ::ADDED === $entry[1]) {
                ++$toRange;
            }
            if (Differ::REMOVED === $entry[1]) {
                ++$fromRange;
            }
        }
        if (\false === $hunkCapture) {
            return;
        }
        $contextStartOffset = $hunkCapture - $this->contextLines < 0 ? $hunkCapture : $this->contextLines;
        $contextEndOffset = min($sameCount, $this->contextLines);
        $fromRange -= $sameCount;
        $toRange -= $sameCount;
        $this->writeHunk($diff, $hunkCapture - $contextStartOffset, $i - $sameCount + $contextEndOffset + 1, $fromStart - $contextStartOffset, $fromRange + $contextStartOffset + $contextEndOffset, $toStart - $contextStartOffset, $toRange + $contextStartOffset + $contextEndOffset, $output);
    }
    private function writeHunk(array $diff, int $diffStartIndex, int $diffEndIndex, int $fromStart, int $fromRange, int $toStart, int $toRange, $output) : void
    {
        if ($this->addLineNumbers) {
            fwrite($output, '@@ -' . $fromStart);
            if (!$this->collapseRanges || 1 !== $fromRange) {
                fwrite($output, ',' . $fromRange);
            }
            fwrite($output, ' +' . $toStart);
            if (!$this->collapseRanges || 1 !== $toRange) {
                fwrite($output, ',' . $toRange);
            }
            fwrite($output, " @@\n");
        } else {
            fwrite($output, "@@ @@\n");
        }
        for ($i = $diffStartIndex; $i < $diffEndIndex; ++$i) {
            if ($diff[$i][1] === Differ::ADDED) {
                fwrite($output, '+' . $diff[$i][0]);
            } elseif ($diff[$i][1] === Differ::REMOVED) {
                fwrite($output, '-' . $diff[$i][0]);
            } elseif ($diff[$i][1] === Differ::OLD) {
                fwrite($output, ' ' . $diff[$i][0]);
            } elseif ($diff[$i][1] === Differ::NO_LINE_END_EOF_WARNING) {
                fwrite($output, "\n");
            } else {
                fwrite($output, ' ' . $diff[$i][0]);
            }
        }
    }
}
