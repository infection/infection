<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Configuration\Schema;

use function array_filter;
use function array_map;
use function array_values;
use _HumbugBox9658796bb9f0\Infection\Configuration\Entry\Logs;
use _HumbugBox9658796bb9f0\Infection\Configuration\Entry\PhpUnit;
use _HumbugBox9658796bb9f0\Infection\Configuration\Entry\Source;
use _HumbugBox9658796bb9f0\Infection\Configuration\Entry\StrykerConfig;
use stdClass;
use function trim;
class SchemaConfigurationFactory
{
    public function create(string $path, stdClass $rawConfig) : SchemaConfiguration
    {
        return new SchemaConfiguration($path, $rawConfig->timeout ?? null, self::createSource($rawConfig->source), self::createLogs($rawConfig->logs ?? new stdClass()), self::normalizeString($rawConfig->tmpDir ?? null), self::createPhpUnit($rawConfig->phpUnit ?? new stdClass()), $rawConfig->ignoreMsiWithNoMutations ?? null, $rawConfig->minMsi ?? null, $rawConfig->minCoveredMsi ?? null, (array) ($rawConfig->mutators ?? []), $rawConfig->testFramework ?? null, self::normalizeString($rawConfig->bootstrap ?? null), self::normalizeString($rawConfig->initialTestsPhpOptions ?? null), self::normalizeString($rawConfig->testFrameworkOptions ?? null));
    }
    private static function createSource(stdClass $source) : Source
    {
        return new Source(self::normalizeStringArray($source->directories ?? []), self::normalizeStringArray($source->excludes ?? []));
    }
    private static function createLogs(stdClass $logs) : Logs
    {
        return new Logs(self::normalizeString($logs->text ?? null), self::normalizeString($logs->html ?? null), self::normalizeString($logs->summary ?? null), self::normalizeString($logs->json ?? null), self::normalizeString($logs->debug ?? null), self::normalizeString($logs->perMutator ?? null), $logs->github ?? \false, self::createStrykerConfig($logs->stryker ?? null));
    }
    private static function createStrykerConfig(?stdClass $stryker) : ?StrykerConfig
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
    private static function createPhpUnit(stdClass $phpUnit) : PhpUnit
    {
        return new PhpUnit(self::normalizeString($phpUnit->configDir ?? null), self::normalizeString($phpUnit->customPath ?? null));
    }
    private static function normalizeStringArray(array $values) : array
    {
        $normalizedValue = array_filter(array_map('trim', $values));
        return array_values($normalizedValue);
    }
    private static function normalizeString(?string $value) : ?string
    {
        if ($value === null) {
            return null;
        }
        $normalizedValue = trim($value);
        return $normalizedValue === '' ? null : $normalizedValue;
    }
}
