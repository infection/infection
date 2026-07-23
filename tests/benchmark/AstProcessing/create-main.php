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

namespace Infection\Benchmark\AstProcessing;

use function array_key_exists;
use function array_map;
use function array_merge;
use function array_slice;
use function class_exists;
use Closure;
use function count;
use function function_exists;
use Infection\Container\Container;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\TestFramework\Tracing\TraceProvider;
use function iterator_to_array;
use function max;
use function min;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use Psr\Log\NullLogger;
use function round;
use function Safe\preg_replace;
use function sprintf;
use Symfony\Component\Console\Output\NullOutput;
use function usort;
use Webmozart\Assert\Assert;

require_once __DIR__ . '/../../../vendor/autoload.php';

if (!class_exists(NodeCountingVisitor::class, false)) {
    final class NodeCountingVisitor extends NodeVisitorAbstract
    {
        private int $count = 0;

        /**
         * @param positive-int $maxCount
         */
        public function __construct(
            private readonly int $maxCount,
        ) {
        }

        public function enterNode(Node $node): ?int
        {
            ++$this->count;

            if ($this->count >= $this->maxCount) {
                return NodeVisitor::STOP_TRAVERSAL;
            }

            return null;
        }

        public function getCount(): int
        {
            return $this->count;
        }
    }
}

if (!function_exists('Infection\Benchmark\AstProcessing\createContainer')) {
    function createContainer(): Container
    {
        return Container::create()->withValues(
            logger: new NullLogger(),
            output: new NullOutput(),
            configFile: __DIR__ . '/../Tracing/infection.json5',
            existingCoveragePath: __DIR__ . '/../Tracing/coverage',
        );
    }
}

if (!function_exists('Infection\Benchmark\AstProcessing\collectTraces')) {
    /**
     * @return Trace[]
     */
    function collectTraces(): array
    {
        // We need to use a fresh container instance, otherwise our lovely iterators are going to be consumed.
        $traceProvider = createContainer()->get(TraceProvider::class);

        return iterator_to_array(
            $traceProvider->provideTraces(),
            preserve_keys: false,
        );
    }
}

if (!function_exists('Infection\Benchmark\AstProcessing\takePercentageOfTraces')) {
    /**
     * @param Trace[] $traces
     *
     * @return Trace[]
     */
    function takePercentageOfTraces(float $percentage, array $traces): array
    {
        $indexedTracesGroupedBySourceType = [];

        foreach ($traces as $index => $trace) {
            $sourceType = getTraceSourceType($trace);

            if (!array_key_exists($sourceType, $indexedTracesGroupedBySourceType)) {
                $indexedTracesGroupedBySourceType[$sourceType] = [];
            }

            $indexedTracesGroupedBySourceType[$sourceType][] = [$index, $trace];
        }

        $selectedIndexedTracesList = [];

        foreach ($indexedTracesGroupedBySourceType as $indexedTraces) {
            $tracesOffset = (int) max(
                0,
                min(
                    count($indexedTraces),
                    round(
                        count($indexedTraces) * $percentage,
                    ),
                ),
            );

            $selectedIndexedTracesList[] = array_slice($indexedTraces, 0, $tracesOffset);
        }

        $selectedIndexedTraces = array_merge(...$selectedIndexedTracesList);
        usort(
            $selectedIndexedTraces,
            static fn (array $left, array $right): int => $left[0] <=> $right[0],
        );

        return array_map(
            static fn (array $indexedTrace): Trace => $indexedTrace[1],
            $selectedIndexedTraces,
        );
    }
}

if (!function_exists('Infection\Benchmark\AstProcessing\getTraceSourceType')) {
    function getTraceSourceType(Trace $trace): string
    {
        $sourceFileInfo = $trace->getSourceFileInfo();
        $sourceFileExtension = $sourceFileInfo->getExtension();
        $sourceFileName = $sourceFileExtension === ''
            ? $sourceFileInfo->getBasename()
            : $sourceFileInfo->getBasename('.' . $sourceFileExtension);
        $sourceFileType = preg_replace('/\d+$/', '', $sourceFileName);

        if ($sourceFileType === $sourceFileName) {
            return '';
        }

        if ($sourceFileExtension === '') {
            return sprintf(
                '%s/%s',
                $sourceFileInfo->getPath(),
                $sourceFileType,
            );
        }

        return sprintf(
            '%s/%s.%s',
            $sourceFileInfo->getPath(),
            $sourceFileType,
            $sourceFileExtension,
        );
    }
}

/**
 * @param positive-int $maxCount
 *
 * @return Closure():(positive-int|0)
 */
return static function (int $maxCount, float $percentage = 1.): Closure {
    Assert::range(
        $percentage,
        0,
        1,
        sprintf(
            'Expected the percentage to be an element of [0., 1.]. Got "%s".',
            $percentage,
        ),
    );

    $container = createContainer();
    $traces = collectTraces();
    $tracesSubset = takePercentageOfTraces($percentage, $traces);
    $fileParser = $container->getFileParser();
    $traverserFactory = $container->getNodeTraverserFactory();

    return static function () use (
        $fileParser,
        $maxCount,
        $tracesSubset,
        $traverserFactory,
    ): int {
        $count = 0;

        foreach ($tracesSubset as $trace) {
            $sourceFile = $trace->getSourceFileInfo();
            [$initialStatements] = $fileParser->parse($sourceFile);

            $traverserFactory
                ->createEnrichmentTraverser($sourceFile, $trace)
                ->traverse($initialStatements);

            $remainingNodeCount = $maxCount - $count;
            Assert::positiveInteger($remainingNodeCount);

            $nodeCountingVisitor = new NodeCountingVisitor($remainingNodeCount);

            $traverserFactory
                ->createMutationTraverser($nodeCountingVisitor)
                ->traverse($initialStatements);

            $count += $nodeCountingVisitor->getCount();

            if ($count >= $maxCount) {
                break;
            }
        }

        return $count;
    };
};
