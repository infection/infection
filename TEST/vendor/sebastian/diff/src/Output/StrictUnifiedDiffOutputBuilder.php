<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\SebastianBergmann\Diff\Output;

use function array_merge;
use function array_splice;
use function count;
use function fclose;
use function fopen;
use function fwrite;
use function is_bool;
use function is_int;
use function is_string;
use function max;
use function min;
use function sprintf;
use function stream_get_contents;
use function substr;
use _HumbugBox9658796bb9f0\SebastianBergmann\Diff\ConfigurationException;
use _HumbugBox9658796bb9f0\SebastianBergmann\Diff\Differ;
final class StrictUnifiedDiffOutputBuilder implements DiffOutputBuilderInterface
{
    private static $default = ['collapseRanges' => \true, 'commonLineThreshold' => 6, 'contextLines' => 3, 'fromFile' => null, 'fromFileDate' => null, 'toFile' => null, 'toFileDate' => null];
    private $changed;
    private $collapseRanges;
    private $commonLineThreshold;
    private $header;
    private $contextLines;
    public function __construct(array $options = [])
    {
        $options = array_merge(self::$default, $options);
        if (!is_bool($options['collapseRanges'])) {
            throw new ConfigurationException('collapseRanges', 'a bool', $options['collapseRanges']);
        }
        if (!is_int($options['contextLines']) || $options['contextLines'] < 0) {
            throw new ConfigurationException('contextLines', 'an int >= 0', $options['contextLines']);
        }
        if (!is_int($options['commonLineThreshold']) || $options['commonLineThreshold'] <= 0) {
            throw new ConfigurationException('commonLineThreshold', 'an int > 0', $options['commonLineThreshold']);
        }
        $this->assertString($options, 'fromFile');
        $this->assertString($options, 'toFile');
        $this->assertStringOrNull($options, 'fromFileDate');
        $this->assertStringOrNull($options, 'toFileDate');
        $this->header = sprintf("--- %s%s\n+++ %s%s\n", $options['fromFile'], null === $options['fromFileDate'] ? '' : "\t" . $options['fromFileDate'], $options['toFile'], null === $options['toFileDate'] ? '' : "\t" . $options['toFileDate']);
        $this->collapseRanges = $options['collapseRanges'];
        $this->commonLineThreshold = $options['commonLineThreshold'];
        $this->contextLines = $options['contextLines'];
    }
    public function getDiff(array $diff) : string
    {
        if (0 === count($diff)) {
            return '';
        }
        $this->changed = \false;
        $buffer = fopen('php://memory', 'r+b');
        fwrite($buffer, $this->header);
        $this->writeDiffHunks($buffer, $diff);
        if (!$this->changed) {
            fclose($buffer);
            return '';
        }
        $diff = stream_get_contents($buffer, -1, 0);
        fclose($buffer);
        $last = substr($diff, -1);
        return "\n" !== $last && "\r" !== $last ? $diff . "\n" : $diff;
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
            $this->changed = \true;
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
        fwrite($output, '@@ -' . $fromStart);
        if (!$this->collapseRanges || 1 !== $fromRange) {
            fwrite($output, ',' . $fromRange);
        }
        fwrite($output, ' +' . $toStart);
        if (!$this->collapseRanges || 1 !== $toRange) {
            fwrite($output, ',' . $toRange);
        }
        fwrite($output, " @@\n");
        for ($i = $diffStartIndex; $i < $diffEndIndex; ++$i) {
            if ($diff[$i][1] === Differ::ADDED) {
                $this->changed = \true;
                fwrite($output, '+' . $diff[$i][0]);
            } elseif ($diff[$i][1] === Differ::REMOVED) {
                $this->changed = \true;
                fwrite($output, '-' . $diff[$i][0]);
            } elseif ($diff[$i][1] === Differ::OLD) {
                fwrite($output, ' ' . $diff[$i][0]);
            } elseif ($diff[$i][1] === Differ::NO_LINE_END_EOF_WARNING) {
                $this->changed = \true;
                fwrite($output, $diff[$i][0]);
            }
        }
    }
    private function assertString(array $options, string $option) : void
    {
        if (!is_string($options[$option])) {
            throw new ConfigurationException($option, 'a string', $options[$option]);
        }
    }
    private function assertStringOrNull(array $options, string $option) : void
    {
        if (null !== $options[$option] && !is_string($options[$option])) {
            throw new ConfigurationException($option, 'a string or <null>', $options[$option]);
        }
    }
}
