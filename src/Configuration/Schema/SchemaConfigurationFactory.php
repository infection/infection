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
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use stdClass;
use function trim;

/**
 * @final
 */
class SchemaConfigurationFactory
{
    public function create(string $path, stdClass $rawConfig): SchemaConfiguration
    {
        return new SchemaConfiguration(
            $path,
            $rawConfig->timeout ?? null,
            self::createSource($rawConfig->source),
            self::createLogs($rawConfig->logs ?? new stdClass()),
            self::normalizeString($rawConfig->tmpDir ?? null),
            self::createPhpUnit($rawConfig->phpUnit ?? new stdClass()),
            (array) ($rawConfig->mutators ?? []),
            $rawConfig->testFramework ?? null,
            self::normalizeString($rawConfig->bootstrap ?? null),
            self::normalizeString($rawConfig->initialTestsPhpOptions ?? null),
            self::normalizeString($rawConfig->testFrameworkOptions ?? null)
        );
    }

    private static function createSource(stdClass $source): Source
    {
        return new Source(
            (array) self::normalizeStringArray($source->directories ?? []),
            (array) self::normalizeStringArray($source->excludes ?? [])
        );
    }

    private static function createLogs(stdClass $logs): Logs
    {
        return new Logs(
            self::normalizeString($logs->text ?? null),
            self::normalizeString($logs->summary ?? null),
            self::normalizeString($logs->debug ?? null),
            self::normalizeString($logs->perMutator ?? null),
            self::createBadge($logs->badge ?? null)
        );
    }

    private static function createBadge(?stdClass $badge): ?Badge
    {
        $branch = self::normalizeString($badge->branch ?? null);

        return $branch === null
            ? null
            : new Badge($branch)
        ;
    }

    private static function createPhpUnit(stdClass $phpUnit): PhpUnit
    {
        return new PhpUnit(
            self::normalizeString($phpUnit->configDir ?? null),
            self::normalizeString($phpUnit->customPath ?? null)
        );
    }

    /**
     * @param string[]|null $values
     *
     * @return string[]|null
     */
    private static function normalizeStringArray(
        ?array $values,
        ?array $default = []
    ): ?array {
        if (null === $values) {
            return $default;
        }

        $normalizedValue = array_filter(array_map('trim', $values));

        return $normalizedValue === [] ? $default : array_values($normalizedValue);
    }

    private static function normalizeString(
        ?string $value,
        ?string $default = null
    ): ?string {
        if (null === $value) {
            return $default;
        }

        $normalizedValue = trim($value);

        return $normalizedValue === '' ? $default : $normalizedValue;
    }
}
