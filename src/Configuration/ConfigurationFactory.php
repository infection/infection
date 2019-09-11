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

namespace Infection\Configuration;

use Infection\Configuration\Entry\Mutator\GenericMutator;
use function array_filter;
use function array_map;
use function array_values;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\Mutator\ArrayItemRemoval;
use Infection\Configuration\Entry\Mutator\ArrayItemRemovalSettings;
use Infection\Configuration\Entry\Mutator\BCMath;
use Infection\Configuration\Entry\Mutator\BCMathSettings;
use Infection\Configuration\Entry\Mutator\MBString;
use Infection\Configuration\Entry\Mutator\MBStringSettings;
use Infection\Configuration\Entry\Mutator\Mutators;
use Infection\Configuration\Entry\Mutator\TrueValue;
use Infection\Configuration\Entry\Mutator\TrueValueSettings;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use stdClass;
use function is_bool;
use function strpos;
use function trim;

/**
 * @final
 */
class ConfigurationFactory
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
            self::createMutators($rawConfig->mutators ?? new stdClass()),
            $rawConfig->testFramework ?? null,
            self::normalizeString($rawConfig->bootstrap ?? null),
            self::normalizeString($rawConfig->initialTestsPhpOptions ?? null),
            self::normalizeString($rawConfig->testFrameworkOptions ?? null)
        );
    }

    private static function createSource(stdClass $source): Source
    {
        return new Source(
            self::normalizeStringArray($source->directories),
            self::normalizeStringArray($source->excludes ?? [])
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

    private static function createMutators(stdClass $mutators): Mutators
    {
        $profiles = [];

        $trueValue = null;
        $arrayItemRemoval = null;
        $bcMath = null;
        $mbString = null;
        $generics = [];

        foreach ($mutators as $key => $value) {
            if (0 === strpos($key, '@')) {
                $profiles[$key] = $value;

                continue;
            }

            if ('TrueValue' === $key) {
                $trueValue = self::createTrueValue($value);

                continue;
            }

            if ('ArrayItemRemoval' === $key) {
                $arrayItemRemoval = self::createArrayItemRemoval($value);

                continue;
            }

            if ('BCMath' === $key) {
                $bcMath = self::createBCMath($value);

                continue;
            }

            if ('MBString' === $key) {
                $mbString = self::createMBString($value);

                continue;
            }

            $generics[] = self::createGeneric($key, $value);
        }

        return new Mutators(
            $profiles,
            $trueValue,
            $arrayItemRemoval,
            $bcMath,
            $mbString,
            ...$generics
        );
    }

    /**
     * @param bool|stdClass $value
     */
    private static function createTrueValue($value): TrueValue
    {
        if (is_bool($value)) {
            $enabled = $value;

            $ignore = [];

            $settings = $value
                ? new TrueValueSettings(true, true)
                : new TrueValueSettings(false, false)
            ;
        } else {
            $enabled = true;

            $ignore = self::normalizeStringArray($value->ignore ?? []);

            $settings = new TrueValueSettings(
                $value->settings->in_array ?? true,
                $value->settings->array_search ?? true
            );
        }

        return new TrueValue($enabled, $ignore, $settings);
    }

    /**
     * @param bool|stdClass $value
     */
    private static function createArrayItemRemoval($value): ArrayItemRemoval
    {
        if (is_bool($value)) {
            $enabled = $value;

            $ignore = [];

            $settings = new ArrayItemRemovalSettings('all', null);
        } else {
            $enabled = true;

            $ignore = self::normalizeStringArray($value->ignore ?? []);

            $settings = new ArrayItemRemovalSettings(
                $value->settings->remove ?? 'all',
                $value->settings->limit ?? null
            );
        }

        return new ArrayItemRemoval($enabled, $ignore, $settings);
    }

    /**
     * @param bool|stdClass $value
     */
    private static function createBCMath($value): BCMath
    {
        if (is_bool($value)) {
            $enabled = $value;

            $ignore = [];

            $settings = $value
                ? new BCMathSettings(
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true
                )
                : new BCMathSettings(
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false
                )
            ;
        } else {
            $enabled = true;

            $ignore = self::normalizeStringArray($value->ignore ?? []);

            $settings = new BCMathSettings(
                $value->settings->bcadd ?? true,
                $value->settings->bccomp ?? true,
                $value->settings->bcdiv ?? true,
                $value->settings->bcmod ?? true,
                $value->settings->bcmul ?? true,
                $value->settings->bcpow ?? true,
                $value->settings->bcsub ?? true,
                $value->settings->bcsqrt ?? true,
                $value->settings->bcpowmod ?? true
            );
        }

        return new BCMath($enabled, $ignore, $settings);
    }

    /**
     * @param bool|stdClass $value
     */
    private static function createMBString($value): MBString
    {
        if (is_bool($value)) {
            $enabled = $value;

            $ignore = [];

            $settings = $value
                ? new MBStringSettings(
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true
                )
                : new MBStringSettings(
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false
                )
            ;
        } else {
            $enabled = true;

            $ignore = self::normalizeStringArray($value->ignore ?? []);

            $settings = new MBStringSettings(
                $value->settings->mb_chr ?? true,
                $value->settings->mb_ord ?? true,
                $value->settings->mb_parse_str ?? true,
                $value->settings->mb_send_mail ?? true,
                $value->settings->mb_strcut ?? true,
                $value->settings->mb_stripos ?? true,
                $value->settings->mb_stristr ?? true,
                $value->settings->mb_strlen ?? true,
                $value->settings->mb_strpos ?? true,
                $value->settings->mb_strrchr ?? true,
                $value->settings->mb_strripos ?? true,
                $value->settings->mb_strrpos ?? true,
                $value->settings->mb_strstr ?? true,
                $value->settings->mb_strtolower ?? true,
                $value->settings->mb_strtoupper ?? true,
                $value->settings->mb_substr_count ?? true,
                $value->settings->mb_substr ?? true,
                $value->settings->mb_convert_case ?? true
            );
        }

        return new MBString($enabled, $ignore, $settings);
    }

    /**
     * @param bool|stdClass $value
     */
    private static function createGeneric(string $name, $value): GenericMutator
    {
        if (is_bool($value)) {
            $enabled = $value;

            $ignore = [];
        } else {
            $enabled = true;

            $ignore = self::normalizeStringArray($value->ignore ?? []);
        }

        return new GenericMutator($name, $enabled, $ignore);
    }

    /**
     * @param string[]|null $values
     *
     * @return string[]|null
     */
    private static function normalizeStringArray(
        ?array $values,
        ?array $default = []
    ): array {
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
