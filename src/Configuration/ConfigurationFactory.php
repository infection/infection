<?php

declare(strict_types=1);

namespace Infection\Configuration;

use function array_map;
use function array_unique;
use function array_values;
use const INF;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\BCMathSettings;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\Mutator\ArrayItemRemoval;
use Infection\Configuration\Entry\Mutator\ArrayItemRemovalSettings;
use Infection\Configuration\Entry\Mutator\BCMath;
use Infection\Configuration\Entry\Mutator\MBString;
use Infection\Configuration\Entry\Mutator\MBStringSettings;
use Infection\Configuration\Entry\Mutator\Mutators;
use Infection\Configuration\Entry\Mutator\TrueValue;
use Infection\Configuration\Entry\Mutator\TrueValueSettings;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\RawConfiguration\RawConfiguration;
use function is_bool;
use const PHP_INT_MAX;
use stdClass;
use function strpos;

/**
 * @final
 */
class ConfigurationFactory
{
    public function create(stdClass $rawConfig): Configuration
    {
        return new Configuration(
            $rawConfig->timeout ?? null,
            new Source(
                $rawConfig->source->directories,
                $rawConfig->source->excludes ?? []
            ),
            self::createLogs($rawConfig->logs ?? new stdClass()),
            $rawConfig->tmpDir ?? null,
            self::createPhpUnit($rawConfig->phpUnit ?? new stdClass()),
            self::createMutators($rawConfig->mutators ?? new stdClass()),
            $rawConfig->testFramework ?? null,
            $rawConfig->bootstrap ?? null,
            $rawConfig->initialTestsPhpOptions ?? null,
            $rawConfig->testFrameworkOptions ?? null
        );
    }

    private static function createLogs(stdClass $logs): Logs
    {
        return new Logs(
            $logs->text ?? null,
            $logs->summary ?? null,
            $logs->debug ?? null,
            $logs->perMutator ?? null,
            self::createBadge($logs->badge ?? null)
        );
    }

    private static function createBadge(?stdClass $badge): ?Badge
    {
        return null === $badge ? null : new Badge($badge->branch);
    }

    private static function createPhpUnit(stdClass $phpUnit): PhpUnit
    {
        return new PhpUnit(
            $phpUnit->configDir ?? null,
            $phpUnit->customPath ?? null
        );
    }

    private static function createMutators(stdClass $mutators): Mutators
    {
        $profiles = [];

        $trueValue = null;
        $arrayItemRemoval = null;
        $bcMath = null;
        $mbString = null;

        foreach ($mutators as $key => $value) {
            if (0 === strpos($key, '@')) {
                $profiles[$key] = $value;

                continue;
            }

            if ('TrueValue' === $key) {
                $trueValue = self::createTrueValue($value);
            }

            if ('ArrayItemRemoval' === $key) {
                $arrayItemRemoval = self::createArrayItemRemoval($value);
            }

            if ('BCMath' === $key) {
                $bcMath = self::createBCMath($value);
            }

            if ('MBString' === $key) {
                $mbString = self::createMBString($value);
            }

            // TODO: throw an error
        }

        return new Mutators(
            $profiles,
            $trueValue,
            $arrayItemRemoval,
            $bcMath,
            $mbString
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

            $ignore = array_values(array_unique(array_map('trim', $value->ignore)));

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

            $settings = $value
                ? new ArrayItemRemovalSettings('all', INF)
                : new ArrayItemRemovalSettings('all', 1)
            ;
        } else {
            $enabled = true;

            $ignore = array_values(array_unique(array_map('trim', $value->ignore)));

            $settings = new ArrayItemRemovalSettings(
                $value->settings->remove ?? 'all',
                $value->settings->limit ?? INF
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

            $ignore = array_values(array_unique(array_map('trim', $value->ignore)));

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

            $ignore = array_values(array_unique(array_map('trim', $value->ignore)));

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
}