<?php

namespace HumbugBox420\Composer\Semver;

use HumbugBox420\Composer\Semver\Constraint\Constraint;
class Semver
{
    const SORT_ASC = 1;
    const SORT_DESC = -1;
    private static $versionParser;
    public static function satisfies($version, $constraints)
    {
        if (null === self::$versionParser) {
            self::$versionParser = new VersionParser();
        }
        $versionParser = self::$versionParser;
        $provider = new Constraint('==', $versionParser->normalize($version));
        $parsedConstraints = $versionParser->parseConstraints($constraints);
        return $parsedConstraints->matches($provider);
    }
    public static function satisfiedBy(array $versions, $constraints)
    {
        $versions = \array_filter($versions, function ($version) use($constraints) {
            return Semver::satisfies($version, $constraints);
        });
        return \array_values($versions);
    }
    public static function sort(array $versions)
    {
        return self::usort($versions, self::SORT_ASC);
    }
    public static function rsort(array $versions)
    {
        return self::usort($versions, self::SORT_DESC);
    }
    private static function usort(array $versions, $direction)
    {
        if (null === self::$versionParser) {
            self::$versionParser = new VersionParser();
        }
        $versionParser = self::$versionParser;
        $normalized = array();
        foreach ($versions as $key => $version) {
            $normalizedVersion = $versionParser->normalize($version);
            $normalizedVersion = $versionParser->normalizeDefaultBranch($normalizedVersion);
            $normalized[] = array($normalizedVersion, $key);
        }
        \usort($normalized, function (array $left, array $right) use($direction) {
            if ($left[0] === $right[0]) {
                return 0;
            }
            if (Comparator::lessThan($left[0], $right[0])) {
                return -$direction;
            }
            return $direction;
        });
        $sorted = array();
        foreach ($normalized as $item) {
            $sorted[] = $versions[$item[1]];
        }
        return $sorted;
    }
}
