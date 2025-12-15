<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Benchmark\ParseGitDiff;

use function abs;
use function array_map;
use function array_sum;
use Closure;
use function count;
use function floor;
use function fwrite;
use function implode;
use InvalidArgumentException;
use function log;
use function max;
use function md5;
use function min;
use function number_format;
use Random\Randomizer;
use function round;
use RuntimeException;
use function sort;
use const SORT_NUMERIC;
use function sprintf;
use const STDERR;
use function str_pad;
use const STR_PAD_LEFT;
use function substr;
use function time;

/**
 * Configuration for git diff generation.
 */
final readonly class Configuration
{
    /**
     * @param positive-int $targetTotalLines Total lines changed across all files.
     * @param positive-int $targetTotalFiles Total number of files changed.
     * @param float $addedFileRatio Ratio of new files (0.0 to 1.0).
     * @param float $gpdK Shape parameter (k) for Generalized Pareto Distribution.
     * @param float $gpdTheta Threshold parameter (θ) for GPD.
     * @param float $gpdSigma Scale parameter (σ) for GPD.
     * @param positive-int $minLinesPerFile Minimum lines changed per file.
     * @param positive-int $maxLinesPerFile Maximum lines changed per file.
     * @param positive-int $directoryDepthMax Maximum directory nesting.
     * @param positive-int $directoryPoolSize Number of unique directory names.
     * @param positive-int $filePoolSize Number of unique file names.
     * @param non-empty-list<non-empty-string> $fileExtensions Available file extensions.
     * @param positive-int $maxHunkSize Maximum lines per hunk.
     * @param float $modifiedFileAddRatio In modified files, ratio of additions vs deletions.
     */
    private function __construct(
        public int $targetTotalLines,
        public int $targetTotalFiles,
        public float $addedFileRatio,
        public float $gpdK,
        public float $gpdTheta,
        public float $gpdSigma,
        public int $minLinesPerFile,
        public int $maxLinesPerFile,
        public int $directoryDepthMax,
        public int $directoryPoolSize,
        public int $filePoolSize,
        public array $fileExtensions,
        public int $maxHunkSize,
        public float $modifiedFileAddRatio,
    ) {
    }

    /**
     * Create and validate a new Configuration instance.
     *
     * @param positive-int $targetTotalLines
     * @param positive-int $targetTotalFiles
     * @param positive-int $minLinesPerFile
     * @param positive-int $maxLinesPerFile
     * @param positive-int $directoryDepthMax
     * @param positive-int $directoryPoolSize
     * @param positive-int $filePoolSize
     * @param non-empty-list<non-empty-string> $fileExtensions
     * @param positive-int $maxHunkSize
     */
    public static function create(
        int $targetTotalLines = 1_000_000,
        int $targetTotalFiles = 10_000,
        float $addedFileRatio = 0.30,
        float $gpdK = 1.4617,
        float $gpdTheta = 0.5,
        float $gpdSigma = 13.854,
        int $minLinesPerFile = 1,
        int $maxLinesPerFile = 5000,
        int $directoryDepthMax = 5,
        int $directoryPoolSize = 50,
        int $filePoolSize = 200,
        array $fileExtensions = ['php', 'js', 'py', 'java', 'cpp', 'c', 'rb', 'go', 'ts', 'rs'],
        int $maxHunkSize = 100,
        float $modifiedFileAddRatio = 0.6,
    ): self {
        // Validate positive integers
        if ($targetTotalLines <= 0) {
            throw new InvalidArgumentException('targetTotalLines must be a positive integer');
        }

        if ($targetTotalFiles <= 0) {
            throw new InvalidArgumentException('targetTotalFiles must be a positive integer');
        }

        if ($minLinesPerFile <= 0) {
            throw new InvalidArgumentException('minLinesPerFile must be a positive integer');
        }

        if ($maxLinesPerFile <= 0) {
            throw new InvalidArgumentException('maxLinesPerFile must be a positive integer');
        }

        if ($directoryDepthMax <= 0) {
            throw new InvalidArgumentException('directoryDepthMax must be a positive integer');
        }

        if ($directoryPoolSize <= 0) {
            throw new InvalidArgumentException('directoryPoolSize must be a positive integer');
        }

        if ($filePoolSize <= 0) {
            throw new InvalidArgumentException('filePoolSize must be a positive integer');
        }

        if ($maxHunkSize <= 0) {
            throw new InvalidArgumentException('maxHunkSize must be a positive integer');
        }

        // Validate ratios are between 0 and 1
        if ($addedFileRatio < 0 || $addedFileRatio > 1) {
            throw new InvalidArgumentException('addedFileRatio must be between 0 and 1');
        }

        if ($modifiedFileAddRatio < 0 || $modifiedFileAddRatio > 1) {
            throw new InvalidArgumentException('modifiedFileAddRatio must be between 0 and 1');
        }

        // Validate file extensions is non-empty array
        if (count($fileExtensions) === 0) {
            throw new InvalidArgumentException('fileExtensions must be a non-empty array');
        }

        foreach ($fileExtensions as $ext) {
            if ($ext === '') {
                throw new InvalidArgumentException('All file extensions must be non-empty strings');
            }
        }

        // Validate min is less than or equal to max
        if ($minLinesPerFile > $maxLinesPerFile) {
            throw new InvalidArgumentException('minLinesPerFile must be less than or equal to maxLinesPerFile');
        }

        return new self(
            targetTotalLines: $targetTotalLines,
            targetTotalFiles: $targetTotalFiles,
            addedFileRatio: $addedFileRatio,
            gpdK: $gpdK,
            gpdTheta: $gpdTheta,
            gpdSigma: $gpdSigma,
            minLinesPerFile: $minLinesPerFile,
            maxLinesPerFile: $maxLinesPerFile,
            directoryDepthMax: $directoryDepthMax,
            directoryPoolSize: $directoryPoolSize,
            filePoolSize: $filePoolSize,
            fileExtensions: $fileExtensions,
            maxHunkSize: $maxHunkSize,
            modifiedFileAddRatio: $modifiedFileAddRatio,
        );
    }
}

/**
 * Handles input/output operations.
 */
final readonly class IO
{
    /**
     * @param Closure(string): mixed $write
     * @param Closure(string): mixed $writeError
     */
    public function __construct(
        private Closure $write,
        private Closure $writeError,
    ) {
    }

    public static function writeToStdErr(string $content): void
    {
        // @phpstan-ignore theCodingMachineSafe.function
        fwrite(STDERR, $content);
    }

    /**
     * Write content to standard output.
     */
    public function write(string $content): void
    {
        ($this->write)($content);
    }

    /**
     * Write content to standard error.
     */
    public function writeError(string $content): void
    {
        ($this->writeError)($content);
    }
}

/**
 * Git Diff Generator with Generalized Pareto Distribution.
 *
 * Generates synthetic git diff output following GPD for commit sizes.
 *
 * @see https://www.mathworks.com/help/stats/generalized-pareto-distribution.html
 */
final readonly class Generator
{
    /**
     * Generate the complete git diff output.
     */
    public static function generate(Configuration $config, Randomizer $randomizer, IO $io): void
    {
        // Scale to match target total lines
        $fileSizes = self::scaleFileSizes(
            $config,
            // Generate file sizes following Generalized Pareto Distribution
            self::generateFileSizes($config, $randomizer),
        );

        // Generate and output the diff
        self::generateDiffOutput($config, $randomizer, $io, $fileSizes);

        // Output statistics to stderr
        self::outputStatistics($config, $io, $fileSizes);
    }

    /**
     * Generate file sizes following Generalized Pareto Distribution.
     *
     * @return non-empty-list<int>
     */
    private static function generateFileSizes(Configuration $config, Randomizer $randomizer): array
    {
        $fileSizes = [];

        for ($i = 0; $i < $config->targetTotalFiles; ++$i) {
            $size = self::sampleGPD(
                $randomizer,
                $config->gpdK,
                $config->gpdSigma,
                $config->gpdTheta,
            );

            // Round and apply constraints
            $size = (int) round($size);
            $size = max(
                $config->minLinesPerFile,
                min($config->maxLinesPerFile, $size),
            );

            $fileSizes[] = $size;
        }

        if (count($fileSizes) === 0) {
            throw new InvalidArgumentException('fileSizes must be a non-empty array!');
        }

        return $fileSizes;
    }

    /**
     * Scale file sizes to match target total lines.
     *
     * @param non-empty-list<int> $fileSizes
     *
     * @return non-empty-list<int>
     */
    private static function scaleFileSizes(Configuration $config, array $fileSizes): array
    {
        $currentTotal = array_sum($fileSizes);

        if ($currentTotal === 0) {
            throw new RuntimeException('Generated file sizes sum to zero, cannot scale');
        }

        $scaleFactor = $config->targetTotalLines / $currentTotal;

        return array_map(
            static fn (int $size): int => max(
                $config->minLinesPerFile,
                (int) round($size * $scaleFactor),
            ),
            $fileSizes,
        );
    }

    /**
     * Generate and output the complete diff.
     *
     * @param list<int> $fileSizes
     */
    private static function generateDiffOutput(Configuration $config, Randomizer $randomizer, IO $io, array $fileSizes): void
    {
        $numAddedFiles = (int) round($config->targetTotalFiles * $config->addedFileRatio);

        /** @var array<string, true> */
        $generatedPaths = [];

        for ($i = 0; $i < $config->targetTotalFiles; ++$i) {
            $isNew = ($i < $numAddedFiles);

            // Generate unique file path
            do {
                $filePath = self::generateFilePath($config, $randomizer);
            } while (isset($generatedPaths[$filePath]));
            $generatedPaths[$filePath] = true;

            $totalLines = $fileSizes[$i];

            if ($totalLines < 1) {
                throw new InvalidArgumentException('Generated file size too small');
            }

            // Generate and output the diff for this file
            $io->write(self::generateDiffHeader($filePath, $isNew));
            $io->write(self::generateHunks($config, $randomizer, $totalLines, $isNew));
        }
    }

    /**
     * Output generation statistics to stderr.
     *
     * @param non-empty-list<int> $fileSizes
     */
    private static function outputStatistics(Configuration $config, IO $io, array $fileSizes): void
    {
        $actualTotal = array_sum($fileSizes);
        $numAddedFiles = (int) round($config->targetTotalFiles * $config->addedFileRatio);

        $sortedSizes = $fileSizes;
        sort($sortedSizes, SORT_NUMERIC);
        $median = $sortedSizes[(int) floor(count($sortedSizes) / 2)];

        /** @var array<string, int|float> */
        $stats = [
            'Total Files' => count($fileSizes),
            'Total Lines' => $actualTotal,
            'Added Files' => $numAddedFiles,
            'Modified Files' => count($fileSizes) - $numAddedFiles,
            'Min Lines/File' => min($fileSizes),
            'Max Lines/File' => max($fileSizes),
            'Mean Lines/File' => round(array_sum($fileSizes) / count($fileSizes), 2),
            'Median Lines/File' => $median,
        ];

        $io->writeError("\n=== Generation Statistics ===\n");

        foreach ($stats as $key => $value) {
            $io->writeError(sprintf("%-20s: %s\n", $key, number_format((float) $value)));
        }
        $io->writeError("=============================\n\n");
    }

    /**
     * Generate a sample from Generalized Pareto Distribution using inverse transform sampling.
     *
     * Based on the quantile function (inverse CDF) from MathWorks GPD implementation.
     *
     * @param float $k Shape parameter (k).
     * @param float $sigma Scale parameter (σ).
     * @param float $theta Threshold parameter (θ).
     *
     * @see https://www.mathworks.com/help/stats/generalized-pareto-distribution.html
     */
    private static function sampleGPD(Randomizer $randomizer, float $k, float $sigma, float $theta): float
    {
        // @phpstan-ignore method.notFound
        $probability = $randomizer->nextFloat();

        // Handle shape parameter close to 0 (exponential distribution case)
        if (abs($k) < 1e-10) {
            return $theta - $sigma * log(1 - $probability);
        }

        // Standard GPD quantile function: x = theta - (sigma/k) * (1 - (1-p)^(-k))
        return $theta + ($sigma / $k) * ((1 - $probability) ** (-$k) - 1);
    }

    /**
     * Generate a realistic file path.
     *
     * @return non-empty-string
     */
    private static function generateFilePath(Configuration $config, Randomizer $randomizer): string
    {
        $depth = $randomizer->getInt(1, $config->directoryDepthMax);
        $path = [];

        // Generate directory path
        for ($i = 0; $i < $depth - 1; ++$i) {
            $dirNum = $randomizer->getInt(1, $config->directoryPoolSize);
            $path[] = 'dir' . str_pad((string) $dirNum, 2, '0', STR_PAD_LEFT);
        }

        // Generate filename
        $fileNum = $randomizer->getInt(1, $config->filePoolSize);
        $filename = 'file' . str_pad((string) $fileNum, 3, '0', STR_PAD_LEFT);
        $extIndex = $randomizer->getInt(0, count($config->fileExtensions) - 1);
        $ext = $config->fileExtensions[$extIndex];
        $path[] = $filename . '.' . $ext;

        return implode('/', $path);
    }

    /**
     * Generate diff header for a file.
     *
     * @param non-empty-string $filePath
     *
     * @return non-empty-string
     */
    private static function generateDiffHeader(string $filePath, bool $isNew): string
    {
        $output = "diff --git a/$filePath b/$filePath\n";

        if ($isNew) {
            // New file
            $output .= "new file mode 100644\n";
            $output .= 'index 0000000..';
            $hash = md5($filePath . (string) time());
            $output .= substr($hash, 0, 7) . "\n";
            $output .= "--- /dev/null\n";
            $output .= "+++ b/$filePath\n";
        } else {
            // Modified file
            $oldHash = substr(md5($filePath . 'old'), 0, 7);
            $newHash = substr(md5($filePath . 'new'), 0, 7);
            $output .= "index $oldHash..$newHash 100644\n";
            $output .= "--- a/$filePath\n";
            $output .= "+++ b/$filePath\n";
        }

        return $output;
    }

    /**
     * Generate hunks for a file with --unified=0 format.
     *
     * @param positive-int $totalLines
     */
    private static function generateHunks(Configuration $config, Randomizer $randomizer, int $totalLines, bool $isNew): string
    {
        $output = '';
        $remainingLines = $totalLines;
        $currentLine = 1;

        while ($remainingLines > 0) {
            // Determine hunk size
            $hunkSize = min(
                $remainingLines,
                $randomizer->getInt(1, min($config->maxHunkSize, $remainingLines)),
            );

            if ($isNew) {
                // New file: only additions
                $additions = $hunkSize;
                $oldStart = 0;
                $newStart = $currentLine;

                $output .= "@@ -0,0 +$newStart,$additions @@\n";

                for ($i = 0; $i < $additions; ++$i) {
                    $output .= '+Line ' . ($currentLine + $i) . " content\n";
                }

                $currentLine += $additions;
            } else {
                // Modified file: mix of additions and deletions
                $additions = (int) round($hunkSize * $config->modifiedFileAddRatio);
                $deletions = $hunkSize - $additions;

                $oldStart = $currentLine;
                $newStart = $currentLine;

                if ($deletions > 0 && $additions > 0) {
                    $output .= "@@ -$oldStart,$deletions +$newStart,$additions @@\n";

                    for ($i = 0; $i < $deletions; ++$i) {
                        $output .= '-Old line ' . ($oldStart + $i) . " removed\n";
                    }

                    for ($i = 0; $i < $additions; ++$i) {
                        $output .= '+New line ' . ($newStart + $i) . " added\n";
                    }
                } elseif ($deletions > 0) {
                    $output .= "@@ -$oldStart,$deletions +$newStart,0 @@\n";

                    for ($i = 0; $i < $deletions; ++$i) {
                        $output .= '-Old line ' . ($oldStart + $i) . " removed\n";
                    }
                } else {
                    $output .= "@@ -$oldStart,0 +$newStart,$additions @@\n";

                    for ($i = 0; $i < $additions; ++$i) {
                        $output .= '+New line ' . ($newStart + $i) . " added\n";
                    }
                }

                $currentLine += max($additions, $deletions);
            }

            $remainingLines -= $hunkSize;
        }

        return $output;
    }
}
