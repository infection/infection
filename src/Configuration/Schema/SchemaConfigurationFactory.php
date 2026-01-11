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

namespace Infection\Configuration\Schema;

use function array_filter;
use function array_map;
use function array_values;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\Entry\StrykerConfig;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\TestFrameworkTypes;
use stdClass;
use function trim;
use Webmozart\Assert\Assert;

/**
 * @final
 */
class SchemaConfigurationFactory
{
    /**
     * @param non-empty-string $pathname
     */
    public function create(string $pathname, stdClass $rawConfig): SchemaConfiguration
    {
        return new SchemaConfiguration(
            pathname: $pathname,
            timeout: self::getTimeout($rawConfig),
            source: self::createSource($rawConfig->source),
            logs: self::createLogs($rawConfig->logs ?? new stdClass()),
            tmpDir: self::normalizeString($rawConfig->tmpDir ?? null),
            phpUnit: self::createPhpUnit($rawConfig->phpUnit ?? new stdClass()),
            phpStan: self::createPhpStan($rawConfig->phpStan ?? new stdClass()),
            ignoreMsiWithNoMutations: $rawConfig->ignoreMsiWithNoMutations ?? null,
            minMsi: $rawConfig->minMsi ?? null,
            minCoveredMsi: $rawConfig->minCoveredMsi ?? null,
            timeoutsAsEscaped: $rawConfig->timeoutsAsEscaped ?? null,
            maxTimeouts: $rawConfig->maxTimeouts ?? null,
            mutators: (array) ($rawConfig->mutators ?? []),
            testFramework: self::getTestFramework($rawConfig),
            bootstrap: self::normalizeString($rawConfig->bootstrap ?? null),
            initialTestsPhpOptions: self::normalizeString($rawConfig->initialTestsPhpOptions ?? null),
            testFrameworkExtraOptions: self::normalizeString($rawConfig->testFrameworkOptions ?? null),
            staticAnalysisToolOptions: self::normalizeString($rawConfig->staticAnalysisToolOptions ?? null),
            threads: $rawConfig->threads ?? null,
            staticAnalysisTool: self::getStaticAnalysisTool($rawConfig),
        );
    }

    private static function getTimeout(stdClass $rawConfig): ?float
    {
        $timeout = $rawConfig->timeout ?? null;

        Assert::nullOrGreaterThanEq($timeout, 0);

        return $timeout;
    }

    /**
     * @return TestFrameworkTypes::*|null
     */
    private static function getTestFramework(stdClass $rawConfig): ?string
    {
        $testFramework = $rawConfig->testFramework ?? null;

        // This value is already vetted by the validation of the JSON against
        // the schema.json, hence there is no need to go an extra length about
        // the type.
        // It is more due to very defensive programming habits than necessity.
        Assert::nullOrOneOf($testFramework, TestFrameworkTypes::getTypes());

        // @phpstan-ignore return.type
        return $testFramework;
    }

    /**
     * @return StaticAnalysisToolTypes::*|null
     */
    private static function getStaticAnalysisTool(stdClass $rawConfig): ?string
    {
        $staticAnalysisTool = $rawConfig->staticAnalysisTool ?? null;

        // This value is already vetted by the validation of the JSON against
        // the schema.json, hence there is no need to go an extra length about
        // the type.
        // It is more due to very defensive programming habits than necessity.
        Assert::nullOrOneOf($staticAnalysisTool, StaticAnalysisToolTypes::getTypes());

        // @phpstan-ignore return.type
        return $staticAnalysisTool;
    }

    private static function createSource(stdClass $source): Source
    {
        return new Source(
            self::normalizeStringArray($source->directories ?? []),
            self::normalizeStringArray($source->excludes ?? []),
        );
    }

    private static function createLogs(stdClass $logs): Logs
    {
        return new Logs(
            self::normalizeString($logs->text ?? null),
            self::normalizeString($logs->html ?? null),
            self::normalizeString($logs->summary ?? null),
            self::normalizeString($logs->json ?? null),
            self::normalizeString($logs->gitlab ?? null),
            self::normalizeString($logs->debug ?? null),
            self::normalizeString($logs->perMutator ?? null),
            $logs->github ?? false,
            self::createStrykerConfig($logs->stryker ?? null),
            self::normalizeString($logs->summaryJson ?? null),
        );
    }

    private static function createStrykerConfig(?stdClass $stryker): ?StrykerConfig
    {
        if ($stryker === null) {
            return null;
        }

        $branch = self::normalizeString($stryker->badge ?? $stryker->report ?? null);

        if ($branch === null) {
            return null;
        }

        if (($stryker->badge ?? null) !== null) {
            return StrykerConfig::forBadge($branch);
        }

        return StrykerConfig::forFullReport($branch);
    }

    private static function createPhpUnit(stdClass $phpUnit): PhpUnit
    {
        return new PhpUnit(
            self::normalizeString($phpUnit->configDir ?? null),
            self::normalizeString($phpUnit->customPath ?? null),
        );
    }

    private static function createPhpStan(stdClass $phpStan): PhpStan
    {
        return new PhpStan(
            self::normalizeString($phpStan->configDir ?? null),
            self::normalizeString($phpStan->customPath ?? null),
        );
    }

    /**
     * @param string[] $values
     *
     * @return list<non-empty-string>
     */
    private static function normalizeStringArray(array $values): array
    {
        $normalizedValue = array_filter(array_map(trim(...), $values));

        return array_values($normalizedValue);
    }

    private static function normalizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalizedValue = trim($value);

        return $normalizedValue === '' ? null : $normalizedValue;
    }
}
