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

require_once __DIR__ . '/../vendor/autoload.php';

use Infection\TestFramework\XML\SafeDOMXPath;

class TraversalApproach
{
    private SafeDOMXPath $xpath;

    public function __construct(string $xmlFile)
    {
        $this->xpath = SafeDOMXPath::fromFile($xmlFile);
        $this->xpath->registerNamespace(
            'coverage',
            $this->xpath->document->documentElement->namespaceURI,
        );
    }

    public function getFileByName(string $name): ?DOMElement
    {
        $nodes = $this->xpath->queryList('//coverage:file');

        foreach ($nodes as $node) {
            if ($node->getAttribute('name') === $name) {
                return $node;
            }
        }

        return null;
    }
}

class DirectQueryApproach
{
    private SafeDOMXPath $xpath;

    public function __construct(string $xmlFile)
    {
        $this->xpath = SafeDOMXPath::fromFile($xmlFile);
        $this->xpath->registerNamespace(
            'coverage',
            $this->xpath->document->documentElement->namespaceURI,
        );
    }

    public function getFileByName(string $name): ?DOMElement
    {
        return $this->xpath->queryElement("//coverage:file[@name='$name']");
    }
}

class Benchmark
{
    private string $xmlFile;

    private array $sampleFileNames = [];

    public function __construct(string $xmlFile)
    {
        $this->xmlFile = $xmlFile;
        $this->initializeSampleFiles();
    }

    public function runBenchmark(int $iterations = 5): array
    {
        $results = [];

        \printf("Running getFileByName() benchmark with %d iterations...\n\n", $iterations);

        $first50Files = \array_slice($this->sampleFileNames, 0, \min(50, \count($this->sampleFileNames)));
        $totalFiles = \count($this->sampleFileNames);

        \printf("Testing with %d files (50 files scenario) and %d files (all files scenario)\n\n",
            \count($first50Files), $totalFiles);

        // Test 1: Query 50 files using traversal approach
        $results['traversal_50_files'] = $this->benchmarkOperation(
            $iterations,
            'Traversal: getFileByName() for 50 files',
            function () use ($first50Files) {
                $approach = new TraversalApproach($this->xmlFile);
                $files = [];

                foreach ($first50Files as $fileName) {
                    $files[] = $approach->getFileByName($fileName);
                }

                return $files;
            },
        );

        // Test 2: Query 50 files using direct query approach
        $results['direct_50_files'] = $this->benchmarkOperation(
            $iterations,
            'Direct Query: getFileByName() for 50 files',
            function () use ($first50Files) {
                $approach = new DirectQueryApproach($this->xmlFile);
                $files = [];

                foreach ($first50Files as $fileName) {
                    $files[] = $approach->getFileByName($fileName);
                }

                return $files;
            },
        );

        // Test 3: Query all files using traversal approach
        $results['traversal_all_files'] = $this->benchmarkOperation(
            $iterations,
            'Traversal: getFileByName() for all files',
            function () {
                $approach = new TraversalApproach($this->xmlFile);
                $files = [];

                foreach ($this->sampleFileNames as $fileName) {
                    $files[] = $approach->getFileByName($fileName);
                }

                return $files;
            },
        );

        // Test 4: Query all files using direct query approach
        $results['direct_all_files'] = $this->benchmarkOperation(
            $iterations,
            'Direct Query: getFileByName() for all files',
            function () {
                $approach = new DirectQueryApproach($this->xmlFile);
                $files = [];

                foreach ($this->sampleFileNames as $fileName) {
                    $files[] = $approach->getFileByName($fileName);
                }

                return $files;
            },
        );

        return $results;
    }

    public function printResults(array $results): void
    {
        \printf("\n" . \str_repeat('=', 80) . "\n");
        \printf("BENCHMARK RESULTS\n");
        \printf(\str_repeat('=', 80) . "\n\n");

        \printf("%-50s %10s %10s %10s %12s\n", 'Test', 'Avg (ms)', 'Min (ms)', 'Max (ms)', 'Memory');
        \printf(\str_repeat('-', 80) . "\n");

        foreach ($results as $result) {
            \printf("%-50s %10.2f %10.2f %10.2f %12s\n",
                \substr($result['description'], 0, 50),
                $result['avg_time'],
                $result['min_time'],
                $result['max_time'],
                $this->formatBytes($result['avg_memory']),
            );
        }

        \printf(\str_repeat('-', 80) . "\n\n");

        // Performance comparison
        $this->printComparison($results);
    }

    private function initializeSampleFiles(): void
    {
        // Get all file names for testing
        $xpath = SafeDOMXPath::fromFile($this->xmlFile);
        $xpath->registerNamespace(
            'coverage',
            $xpath->document->documentElement->namespaceURI,
        );
        $nodes = $xpath->queryList('//coverage:file');

        foreach ($nodes as $node) {
            $this->sampleFileNames[] = $node->getAttribute('name');
        }
        \printf("Found %d files in the XML coverage report\n", \count($this->sampleFileNames));
    }

    private function benchmarkOperation(int $iterations, string $description, callable $operation): array
    {
        \printf("Testing: %s\n", $description);

        $times = [];
        $memories = [];

        // Warmup
        $operation();

        for ($i = 0; $i < $iterations; ++$i) {
            // Force garbage collection before measurement
            \gc_collect_cycles();

            $memStart = \memory_get_usage(false); // Use false for actual memory usage
            $timeStart = \hrtime(true);

            $result = $operation();

            $timeEnd = \hrtime(true);
            $memEnd = \memory_get_usage(false);

            $times[] = ($timeEnd - $timeStart) / 1e6; // Convert to milliseconds
            $memories[] = \max(0, $memEnd - $memStart); // Ensure non-negative

            // Clean up after measurement
            unset($result);
        }

        $avgTime = \array_sum($times) / \count($times);
        $minTime = \min($times);
        $maxTime = \max($times);
        $avgMemory = \array_sum($memories) / \count($memories);

        \printf("  Avg: %.2f ms, Min: %.2f ms, Max: %.2f ms, Avg Memory: %s\n\n",
            $avgTime, $minTime, $maxTime, $this->formatBytes($avgMemory));

        return [
            'avg_time' => $avgTime,
            'min_time' => $minTime,
            'max_time' => $maxTime,
            'avg_memory' => $avgMemory,
            'description' => $description,
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = \floor(\log(\abs($bytes), 1024));

        return \sprintf('%.2f %s', $bytes / 1024 ** $factor, $units[$factor] ?? 'B');
    }

    private function printComparison(array $results): void
    {
        \printf("PERFORMANCE COMPARISON:\n");
        \printf(\str_repeat('-', 40) . "\n");

        // Compare 50 files scenario
        if (isset($results['traversal_50_files']) && isset($results['direct_50_files'])) {
            $traversalTime = $results['traversal_50_files']['avg_time'];
            $directTime = $results['direct_50_files']['avg_time'];
            $ratio = $directTime / $traversalTime;

            \printf("getFileByName() for 50 files:\n");
            \printf("  %s is %.2fx %s (%.2f ms vs %.2f ms)\n\n",
                $ratio > 1 ? 'Traversal' : 'Direct Query',
                $ratio > 1 ? $ratio : 1 / $ratio,
                $ratio > 1 ? 'faster' : 'slower',
                $ratio > 1 ? $traversalTime : $directTime,
                $ratio > 1 ? $directTime : $traversalTime,
            );
        }

        // Compare all files scenario
        if (isset($results['traversal_all_files']) && isset($results['direct_all_files'])) {
            $traversalTime = $results['traversal_all_files']['avg_time'];
            $directTime = $results['direct_all_files']['avg_time'];
            $ratio = $directTime / $traversalTime;

            \printf("getFileByName() for all files:\n");
            \printf("  %s is %.2fx %s (%.2f ms vs %.2f ms)\n\n",
                $ratio > 1 ? 'Traversal' : 'Direct Query',
                $ratio > 1 ? $ratio : 1 / $ratio,
                $ratio > 1 ? 'faster' : 'slower',
                $ratio > 1 ? $traversalTime : $directTime,
                $ratio > 1 ? $directTime : $traversalTime,
            );
        }

        // Performance analysis
        \printf("ANALYSIS:\n");
        \printf(\str_repeat('-', 20) . "\n");

        if (isset($results['traversal_50_files']) && isset($results['traversal_all_files'])) {
            $time50 = $results['traversal_50_files']['avg_time'];
            $timeAll = $results['traversal_all_files']['avg_time'];
            $ratio = $timeAll / $time50;

            \printf("Traversal scaling (50 vs all files): %.2fx slower for all files\n", $ratio);
        }

        if (isset($results['direct_50_files']) && isset($results['direct_all_files'])) {
            $time50 = $results['direct_50_files']['avg_time'];
            $timeAll = $results['direct_all_files']['avg_time'];
            $ratio = $timeAll / $time50;

            \printf("Direct Query scaling (50 vs all files): %.2fx slower for all files\n\n", $ratio);
        }
    }
}

// Run the benchmark
$xmlFile = '/Users/tfidry/Project/Humbug/infection/build/coverage/xml/index.xml';

if (!\file_exists($xmlFile)) {
    \printf("Error: XML coverage file not found at %s\n", $xmlFile);
    \printf("Please generate coverage report first or update the path.\n");

    exit(1);
}

try {
    $benchmark = new Benchmark($xmlFile);
    $results = $benchmark->runBenchmark(10); // Run 10 iterations for better accuracy
    $benchmark->printResults($results);

    \printf("\nRECOMMENDATION:\n");
    \printf("- Use TRAVERSAL approach when querying many files (the overhead is amortized)\n");
    \printf("- Use DIRECT QUERY approach when querying few specific files\n");
    \printf("- Performance difference becomes more significant with larger file counts\n");
    \printf("- Consider your typical usage pattern: few targeted lookups vs. many file queries\n\n");
} catch (Exception $e) {
    \printf("Error running benchmark: %s\n", $e->getMessage());

    exit(1);
}
