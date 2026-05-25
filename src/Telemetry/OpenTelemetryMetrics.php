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

namespace Infection\Telemetry;

use function array_key_exists;
use Infection\Telemetry\Attribute\RunSpanAttributesProvider;
use function is_float;
use function is_int;
use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type Attributes from RunSpanAttributesProvider
 *
 * @internal
 */
final class OpenTelemetryMetrics
{
    private const int NANOSECONDS_PER_SECOND = 1_000_000_000;

    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private const array SUMMARY_METRIC_NAMES = [
        'infection.source_file.count' => '{file}',
        'infection.mutated_file.count' => '{file}',
        'infection.mutation.generated.count' => '{mutation}',
        'infection.mutation.evaluated.count' => '{mutation}',
        'infection.mutation.suppressed.count' => '{mutation}',
        'infection.mutation.eligible.count' => '{mutation}',
        'infection.mutation.ineligible.count' => '{mutation}',
        'infection.mutation.tested_eligible.count' => '{mutation}',
        'infection.mutation.covered.count' => '{mutation}',
        'infection.mutation.tested_not_covered.count' => '{mutation}',
        'infection.mutation.not_covered.count' => '{mutation}',
        'infection.mutation.not_tested.count' => '{mutation}',
        'infection.mutation.killed_by_tests.count' => '{mutation}',
        'infection.mutation.killed_by_static_analysis.count' => '{mutation}',
        'infection.mutation.escaped.count' => '{mutation}',
        'infection.mutation.error.count' => '{mutation}',
        'infection.mutation.timed_out.count' => '{mutation}',
        'infection.mutation.skipped.count' => '{mutation}',
        'infection.mutation.syntax_error.count' => '{mutation}',
        'infection.mutation.ignored.count' => '{mutation}',
        'infection.msi.threshold' => '%',
        'infection.covered_msi.threshold' => '%',
    ];

    /**
     * @var array<non-empty-string, CounterInterface>
     */
    private array $counters = [];

    /**
     * @var array<non-empty-string, HistogramInterface>
     */
    private array $histograms = [];

    /**
     * @var Attributes
     */
    private array $runAttributes = [];

    /**
     * @var array<non-empty-string, true>
     */
    private array $recordedSummaryMetrics = [];

    public function __construct(
        private readonly MeterInterface $meter,
        private readonly MeterProviderInterface $meterProvider,
    ) {
    }

    /**
     * @param Attributes $attributes
     */
    public function startRun(array $attributes): void
    {
        $this->runAttributes = self::filterAttributes(
            $attributes,
            [
                'infection.project.name',
                'infection.version',
                'infection.distribution',
                'infection.thread.count',
                'infection.run.source_filtered',
                'infection.run.progress_enabled',
                'infection.timeouts_as_escaped',
                'infection.initial_tests.skipped',
                'infection.initial_static_analysis.skipped',
                'infection.test_framework.name',
                'infection.static_analysis_tool.name',
            ],
        );
    }

    /**
     * @param Attributes $attributes
     */
    public function recordSpanEnded(
        SpanHandle $span,
        int $endEpochNanos,
        array $attributes,
    ): void {
        $durationInSeconds = ($endEpochNanos - $span->startEpochNanos) / self::NANOSECONDS_PER_SECOND;
        Assert::greaterThanEq($durationInSeconds, 0.0);

        match ($span->name) {
            'infection.run' => $this->recordRun($durationInSeconds, $attributes),
            'infection.source_collection' => $this->recordPhase('source_collection', $durationInSeconds, $attributes),
            'infection.artefact_collection' => $this->recordPhase('artefact_collection', $durationInSeconds, $attributes),
            'infection.initial_tests' => $this->recordPhase('initial_tests', $durationInSeconds, $attributes),
            'infection.initial_static_analysis' => $this->recordPhase('initial_static_analysis', $durationInSeconds, $attributes),
            'infection.mutation_analysis' => $this->recordPhase('mutation_analysis', $durationInSeconds, $attributes),
            'infection.mutation_generation' => $this->recordPhase('mutation_generation', $durationInSeconds, $attributes),
            'infection.ast_processing' => $this->recordPhase('ast_processing', $durationInSeconds, $attributes),
            'infection.ast_processing.file' => $this->recordHistogram('infection.ast.file.duration', 's', $durationInSeconds),
            'infection.ast_processing.file.parsing' => $this->recordHistogram('infection.ast.file.parsing.duration', 's', $durationInSeconds),
            'infection.ast_processing.file.enrichment' => $this->recordHistogram('infection.ast.file.enrichment.duration', 's', $durationInSeconds),
            'infection.mutation_evaluation' => $this->recordPhase('mutation_evaluation', $durationInSeconds, $attributes),
            'infection.mutation_evaluation.mutation' => $this->recordMutation($durationInSeconds, $attributes),
            'infection.mutation_evaluation.mutant_analysis' => $this->recordHistogram('infection.mutant.analysis.duration', 's', $durationInSeconds),
            'infection.mutation_evaluation.mutant_analysis.materialisation' => $this->recordHistogram('infection.mutant.materialisation.duration', 's', $durationInSeconds),
            'infection.mutation_evaluation.mutant_analysis.evaluation' => $this->recordMutantEvaluation($durationInSeconds, $attributes),
            'infection.mutation_evaluation.mutant_analysis.evaluation.process' => $this->recordMutantProcess($durationInSeconds, $attributes),
            'infection.reporting' => $this->recordPhase('reporting', $durationInSeconds, $attributes),
            'infection.reporting.reporter' => $this->recordReporter($durationInSeconds, $attributes),
            default => null,
        };
    }

    public function shutdown(): void
    {
        $this->meterProvider->shutdown();
    }

    /**
     * @param Attributes $attributes
     */
    private function recordRun(
        float $durationInSeconds,
        array $attributes,
    ): void {
        $this->recordHistogram(
            'infection.run.duration',
            's',
            $durationInSeconds,
            $this->runAttributes,
        );
        $this->counter(
            'infection.run.count', '{run}')->add(
                1,
                $this->runAttributes,
            );
        $this->recordSummaryMetrics($attributes);
    }

    /**
     * @param Attributes $attributes
     */
    private function recordPhase(
        string $phase,
        float $durationInSeconds,
        array $attributes,
    ): void {
        $this->recordHistogram(
            'infection.phase.duration',
            's',
            $durationInSeconds,
            [
                ...$this->runAttributes,
                'infection.phase.name' => $phase,
            ],
        );

        $this->recordOptionalHistogram(
            metricName: 'infection.source_file.count',
            unit: '{file}',
            sourceAttributes: $attributes,
            metricAttributes: $this->runAttributes,
        );
        $this->recordOptionalHistogram(
            metricName: 'infection.mutated_file.count',
            unit: '{file}',
            sourceAttributes: $attributes,
            metricAttributes: $this->runAttributes,
        );
        $this->recordOptionalHistogram(
            metricName: 'infection.mutation.generated.count',
            unit: '{mutation}',
            sourceAttributes: $attributes,
            metricAttributes: $this->runAttributes,
        );
    }

    /**
     * @param Attributes $attributes
     */
    private function recordMutation(
        float $durationInSeconds,
        array $attributes,
    ): void {
        $metricAttributes = [
            ...$this->runAttributes,
            ...self::filterAttributes(
                $attributes,
                [
                    'infection.mutation.status',
                    'infection.mutation.msi.category',
                ],
            ),
        ];

        $this->recordHistogram(
            'infection.mutation.evaluation.duration',
            's',
            $durationInSeconds,
            $metricAttributes,
        );
        $this
            ->counter('infection.mutation.count', '{mutation}')
            ->add(1, $metricAttributes);
        $this->recordOptionalHistogram(
            metricName: 'infection.mutation.runtime',
            unit: 's',
            sourceAttributes: $attributes,
            metricAttributes: $metricAttributes,
            deduplicate: false,
        );
    }

    /**
     * @param Attributes $attributes
     */
    private function recordMutantEvaluation(
        float $durationInSeconds,
        array $attributes,
    ): void {
        $this->recordHistogram(
            'infection.mutant.evaluation.duration',
            's',
            $durationInSeconds,
            $this->runAttributes,
        );
        $this->recordOptionalHistogram(
            metricName: 'infection.mutation.queue_wait.duration',
            unit: 's',
            sourceAttributes: $attributes,
            metricAttributes: $this->runAttributes,
            deduplicate: false,
        );
    }

    /**
     * @param Attributes $attributes
     */
    private function recordMutantProcess(
        float $durationInSeconds,
        array $attributes,
    ): void {
        $metricAttributes = [
            ...$this->runAttributes,
            ...self::filterAttributes(
                $attributes,
                [
                    'infection.mutation.process.test_framework',
                    'infection.mutation.process.thread',
                    'infection.mutation.process.timed_out',
                    'process.exit.code',
                ],
            ),
        ];

        $this->recordHistogram(
            'infection.mutant.process.duration',
            's',
            $durationInSeconds,
            $metricAttributes,
        );
        $this
            ->counter('infection.mutant.process.count', '{process}')
            ->add(1, $metricAttributes);
    }

    /**
     * @param Attributes $attributes
     */
    private function recordReporter(
        float $durationInSeconds,
        array $attributes,
    ): void {
        $metricAttributes = self::filterAttributes(
            $attributes,
            ['infection.reporter.name'],
        );

        $this->recordHistogram(
            'infection.reporter.duration',
            's',
            $durationInSeconds,
            [
                ...$this->runAttributes,
                ...$metricAttributes,
            ],
        );
    }

    /**
     * @param Attributes $attributes
     */
    private function recordSummaryMetrics(array $attributes): void
    {
        foreach (self::SUMMARY_METRIC_NAMES as $name => $unit) {
            $this->recordOptionalHistogram(
                metricName: $name,
                unit: $unit,
                sourceAttributes: $attributes,
                metricAttributes: $this->runAttributes,
            );
        }

        $this->recordOptionalHistogram(
            metricName: 'infection.msi',
            unit: '%',
            sourceAttributes: $attributes,
            metricAttributes: $this->runAttributes,
            sourceAttribute: 'infection.msi.value',
        );
        $this->recordOptionalHistogram(
            metricName: 'infection.mutation.coverage_rate',
            unit: '%',
            sourceAttributes: $attributes,
            metricAttributes: $this->runAttributes,
            sourceAttribute: 'infection.mutation.coverage_rate.value',
        );
        $this->recordOptionalHistogram(
            metricName: 'infection.covered_msi',
            unit: '%',
            sourceAttributes: $attributes,
            metricAttributes: $this->runAttributes,
            sourceAttribute: 'infection.covered_msi.value',
        );
    }

    /**
     * @param non-empty-string $metricName
     * @param non-empty-string $unit
     * @param Attributes $sourceAttributes
     * @param Attributes $metricAttributes
     * @param non-empty-string|null $sourceAttribute
     */
    private function recordOptionalHistogram(
        string $metricName,
        string $unit,
        array $sourceAttributes,
        array $metricAttributes = [],
        ?string $sourceAttribute = null,
        bool $deduplicate = true,
    ): void {
        $sourceAttribute ??= $metricName;
        $value = $sourceAttributes[$sourceAttribute] ?? null;

        if (
            !is_int($value) && !is_float($value)
            || $deduplicate && isset($this->recordedSummaryMetrics[$metricName])
        ) {
            return;
        }

        if ($deduplicate) {
            $this->recordedSummaryMetrics[$metricName] = true;
        }

        $this->recordHistogram(
            $metricName,
            $unit,
            $value,
            $metricAttributes,
        );
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $unit
     * @param Attributes $attributes
     */
    private function recordHistogram(
        string $name,
        string $unit,
        float|int $value,
        array $attributes = [],
    ): void {
        $this
            ->histogram($name, $unit)
            ->record($value, $attributes);
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $unit
     */
    private function counter(string $name, string $unit): CounterInterface
    {
        return $this->counters[$name] ??= $this->meter->createCounter($name, $unit);
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $unit
     */
    private function histogram(string $name, string $unit): HistogramInterface
    {
        return $this->histograms[$name] ??= $this->meter->createHistogram($name, $unit);
    }

    /**
     * @param Attributes $attributes
     * @param list<non-empty-string> $keys
     *
     * @return Attributes
     */
    private static function filterAttributes(array $attributes, array $keys): array
    {
        $filteredAttributes = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $attributes)) {
                $filteredAttributes[$key] = $attributes[$key];
            }
        }

        return $filteredAttributes;
    }
}
