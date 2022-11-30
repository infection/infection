<?php

namespace HumbugBox420\Composer\Semver;

use HumbugBox420\Composer\Semver\Constraint\ConstraintInterface;
use HumbugBox420\Composer\Semver\Constraint\MatchAllConstraint;
use HumbugBox420\Composer\Semver\Constraint\MultiConstraint;
use HumbugBox420\Composer\Semver\Constraint\Constraint;
class VersionParser
{
    private static $modifierRegex = '[._-]?(?:(stable|beta|b|RC|alpha|a|patch|pl|p)((?:[.-]?\\d+)*+)?)?([.-]?dev)?';
    private static $stabilitiesRegex = 'stable|RC|beta|alpha|dev';
    /**
    @phpstan-return
    */
    public static function parseStability($version)
    {
        $version = (string) \preg_replace('{#.+$}', '', (string) $version);
        if (\strpos($version, 'dev-') === 0 || '-dev' === \substr($version, -4)) {
            return 'dev';
        }
        \preg_match('{' . self::$modifierRegex . '(?:\\+.*)?$}i', \strtolower($version), $match);
        if (!empty($match[3])) {
            return 'dev';
        }
        if (!empty($match[1])) {
            if ('beta' === $match[1] || 'b' === $match[1]) {
                return 'beta';
            }
            if ('alpha' === $match[1] || 'a' === $match[1]) {
                return 'alpha';
            }
            if ('rc' === $match[1]) {
                return 'RC';
            }
        }
        return 'stable';
    }
    public static function normalizeStability($stability)
    {
        $stability = \strtolower((string) $stability);
        return $stability === 'rc' ? 'RC' : $stability;
    }
    public function normalize($version, $fullVersion = null)
    {
        $version = \trim((string) $version);
        $origVersion = $version;
        if (null === $fullVersion) {
            $fullVersion = $version;
        }
        if (\preg_match('{^([^,\\s]++) ++as ++([^,\\s]++)$}', $version, $match)) {
            $version = $match[1];
        }
        if (\preg_match('{@(?:' . self::$stabilitiesRegex . ')$}i', $version, $match)) {
            $version = \substr($version, 0, \strlen($version) - \strlen($match[0]));
        }
        if (\in_array($version, array('master', 'trunk', 'default'), \true)) {
            $version = 'dev-' . $version;
        }
        if (\stripos($version, 'dev-') === 0) {
            return 'dev-' . \substr($version, 4);
        }
        if (\preg_match('{^([^,\\s+]++)\\+[^\\s]++$}', $version, $match)) {
            $version = $match[1];
        }
        if (\preg_match('{^v?(\\d{1,5})(\\.\\d++)?(\\.\\d++)?(\\.\\d++)?' . self::$modifierRegex . '$}i', $version, $matches)) {
            $version = $matches[1] . (!empty($matches[2]) ? $matches[2] : '.0') . (!empty($matches[3]) ? $matches[3] : '.0') . (!empty($matches[4]) ? $matches[4] : '.0');
            $index = 5;
        } elseif (\preg_match('{^v?(\\d{4}(?:[.:-]?\\d{2}){1,6}(?:[.:-]?\\d{1,3})?)' . self::$modifierRegex . '$}i', $version, $matches)) {
            $version = \preg_replace('{\\D}', '.', $matches[1]);
            $index = 2;
        }
        if (isset($index)) {
            if (!empty($matches[$index])) {
                if ('stable' === $matches[$index]) {
                    return $version;
                }
                $version .= '-' . $this->expandStability($matches[$index]) . (isset($matches[$index + 1]) && '' !== $matches[$index + 1] ? \ltrim($matches[$index + 1], '.-') : '');
            }
            if (!empty($matches[$index + 2])) {
                $version .= '-dev';
            }
            return $version;
        }
        if (\preg_match('{(.*?)[.-]?dev$}i', $version, $match)) {
            try {
                $normalized = $this->normalizeBranch($match[1]);
                if (\strpos($normalized, 'dev-') === \false) {
                    return $normalized;
                }
            } catch (\Exception $e) {
            }
        }
        $extraMessage = '';
        if (\preg_match('{ +as +' . \preg_quote($version) . '(?:@(?:' . self::$stabilitiesRegex . '))?$}', $fullVersion)) {
            $extraMessage = ' in "' . $fullVersion . '", the alias must be an exact version';
        } elseif (\preg_match('{^' . \preg_quote($version) . '(?:@(?:' . self::$stabilitiesRegex . '))? +as +}', $fullVersion)) {
            $extraMessage = ' in "' . $fullVersion . '", the alias source must be an exact version, if it is a branch name you should prefix it with dev-';
        }
        throw new \UnexpectedValueException('Invalid version string "' . $origVersion . '"' . $extraMessage);
    }
    public function parseNumericAliasPrefix($branch)
    {
        if (\preg_match('{^(?P<version>(\\d++\\.)*\\d++)(?:\\.x)?-dev$}i', (string) $branch, $matches)) {
            return $matches['version'] . '.';
        }
        return \false;
    }
    public function normalizeBranch($name)
    {
        $name = \trim((string) $name);
        if (\preg_match('{^v?(\\d++)(\\.(?:\\d++|[xX*]))?(\\.(?:\\d++|[xX*]))?(\\.(?:\\d++|[xX*]))?$}i', $name, $matches)) {
            $version = '';
            for ($i = 1; $i < 5; ++$i) {
                $version .= isset($matches[$i]) ? \str_replace(array('*', 'X'), 'x', $matches[$i]) : '.x';
            }
            return \str_replace('x', '9999999', $version) . '-dev';
        }
        return 'dev-' . $name;
    }
    public function normalizeDefaultBranch($name)
    {
        if ($name === 'dev-master' || $name === 'dev-default' || $name === 'dev-trunk') {
            return '9999999-dev';
        }
        return (string) $name;
    }
    public function parseConstraints($constraints)
    {
        $prettyConstraint = (string) $constraints;
        $orConstraints = \preg_split('{\\s*\\|\\|?\\s*}', \trim((string) $constraints));
        if (\false === $orConstraints) {
            throw new \RuntimeException('Failed to preg_split string: ' . $constraints);
        }
        $orGroups = array();
        foreach ($orConstraints as $constraints) {
            $andConstraints = \preg_split('{(?<!^|as|[=>< ,]) *(?<!-)[, ](?!-) *(?!,|as|$)}', $constraints);
            if (\false === $andConstraints) {
                throw new \RuntimeException('Failed to preg_split string: ' . $constraints);
            }
            if (\count($andConstraints) > 1) {
                $constraintObjects = array();
                foreach ($andConstraints as $constraint) {
                    foreach ($this->parseConstraint($constraint) as $parsedConstraint) {
                        $constraintObjects[] = $parsedConstraint;
                    }
                }
            } else {
                $constraintObjects = $this->parseConstraint($andConstraints[0]);
            }
            if (1 === \count($constraintObjects)) {
                $constraint = $constraintObjects[0];
            } else {
                $constraint = new MultiConstraint($constraintObjects);
            }
            $orGroups[] = $constraint;
        }
        $constraint = MultiConstraint::create($orGroups, \false);
        $constraint->setPrettyString($prettyConstraint);
        return $constraint;
    }
    /**
    @phpstan-return
    */
    private function parseConstraint($constraint)
    {
        if (\preg_match('{^([^,\\s]++) ++as ++([^,\\s]++)$}', $constraint, $match)) {
            $constraint = $match[1];
        }
        if (\preg_match('{^([^,\\s]*?)@(' . self::$stabilitiesRegex . ')$}i', $constraint, $match)) {
            $constraint = '' !== $match[1] ? $match[1] : '*';
            if ($match[2] !== 'stable') {
                $stabilityModifier = $match[2];
            }
        }
        if (\preg_match('{^(dev-[^,\\s@]+?|[^,\\s@]+?\\.x-dev)#.+$}i', $constraint, $match)) {
            $constraint = $match[1];
        }
        if (\preg_match('{^(v)?[xX*](\\.[xX*])*$}i', $constraint, $match)) {
            if (!empty($match[1]) || !empty($match[2])) {
                return array(new Constraint('>=', '0.0.0.0-dev'));
            }
            return array(new MatchAllConstraint());
        }
        $versionRegex = 'v?(\\d++)(?:\\.(\\d++))?(?:\\.(\\d++))?(?:\\.(\\d++))?(?:' . self::$modifierRegex . '|\\.([xX*][.-]?dev))(?:\\+[^\\s]+)?';
        if (\preg_match('{^~>?' . $versionRegex . '$}i', $constraint, $matches)) {
            if (\strpos($constraint, '~>') === 0) {
                throw new \UnexpectedValueException('Could not parse version constraint ' . $constraint . ': ' . 'Invalid operator "~>", you probably meant to use the "~" operator');
            }
            if (isset($matches[4]) && '' !== $matches[4] && null !== $matches[4]) {
                $position = 4;
            } elseif (isset($matches[3]) && '' !== $matches[3] && null !== $matches[3]) {
                $position = 3;
            } elseif (isset($matches[2]) && '' !== $matches[2] && null !== $matches[2]) {
                $position = 2;
            } else {
                $position = 1;
            }
            if (!empty($matches[8])) {
                $position++;
            }
            $stabilitySuffix = '';
            if (empty($matches[5]) && empty($matches[7]) && empty($matches[8])) {
                $stabilitySuffix .= '-dev';
            }
            $lowVersion = $this->normalize(\substr($constraint . $stabilitySuffix, 1));
            $lowerBound = new Constraint('>=', $lowVersion);
            $highPosition = \max(1, $position - 1);
            $highVersion = $this->manipulateVersionString($matches, $highPosition, 1) . '-dev';
            $upperBound = new Constraint('<', $highVersion);
            return array($lowerBound, $upperBound);
        }
        if (\preg_match('{^\\^' . $versionRegex . '($)}i', $constraint, $matches)) {
            if ('0' !== $matches[1] || '' === $matches[2] || null === $matches[2]) {
                $position = 1;
            } elseif ('0' !== $matches[2] || '' === $matches[3] || null === $matches[3]) {
                $position = 2;
            } else {
                $position = 3;
            }
            $stabilitySuffix = '';
            if (empty($matches[5]) && empty($matches[7]) && empty($matches[8])) {
                $stabilitySuffix .= '-dev';
            }
            $lowVersion = $this->normalize(\substr($constraint . $stabilitySuffix, 1));
            $lowerBound = new Constraint('>=', $lowVersion);
            $highVersion = $this->manipulateVersionString($matches, $position, 1) . '-dev';
            $upperBound = new Constraint('<', $highVersion);
            return array($lowerBound, $upperBound);
        }
        if (\preg_match('{^v?(\\d++)(?:\\.(\\d++))?(?:\\.(\\d++))?(?:\\.[xX*])++$}', $constraint, $matches)) {
            if (isset($matches[3]) && '' !== $matches[3] && null !== $matches[3]) {
                $position = 3;
            } elseif (isset($matches[2]) && '' !== $matches[2] && null !== $matches[2]) {
                $position = 2;
            } else {
                $position = 1;
            }
            $lowVersion = $this->manipulateVersionString($matches, $position) . '-dev';
            $highVersion = $this->manipulateVersionString($matches, $position, 1) . '-dev';
            if ($lowVersion === '0.0.0.0-dev') {
                return array(new Constraint('<', $highVersion));
            }
            return array(new Constraint('>=', $lowVersion), new Constraint('<', $highVersion));
        }
        if (\preg_match('{^(?P<from>' . $versionRegex . ') +- +(?P<to>' . $versionRegex . ')($)}i', $constraint, $matches)) {
            $lowStabilitySuffix = '';
            if (empty($matches[6]) && empty($matches[8]) && empty($matches[9])) {
                $lowStabilitySuffix = '-dev';
            }
            $lowVersion = $this->normalize($matches['from']);
            $lowerBound = new Constraint('>=', $lowVersion . $lowStabilitySuffix);
            $empty = function ($x) {
                return $x === 0 || $x === '0' ? \false : empty($x);
            };
            if (!$empty($matches[12]) && !$empty($matches[13]) || !empty($matches[15]) || !empty($matches[17]) || !empty($matches[18])) {
                $highVersion = $this->normalize($matches['to']);
                $upperBound = new Constraint('<=', $highVersion);
            } else {
                $highMatch = array('', $matches[11], $matches[12], $matches[13], $matches[14]);
                $this->normalize($matches['to']);
                $highVersion = $this->manipulateVersionString($highMatch, $empty($matches[12]) ? 1 : 2, 1) . '-dev';
                $upperBound = new Constraint('<', $highVersion);
            }
            return array($lowerBound, $upperBound);
        }
        if (\preg_match('{^(<>|!=|>=?|<=?|==?)?\\s*(.*)}', $constraint, $matches)) {
            try {
                try {
                    $version = $this->normalize($matches[2]);
                } catch (\UnexpectedValueException $e) {
                    if (\substr($matches[2], -4) === '-dev' && \preg_match('{^[0-9a-zA-Z-./]+$}', $matches[2])) {
                        $version = $this->normalize('dev-' . \substr($matches[2], 0, -4));
                    } else {
                        throw $e;
                    }
                }
                $op = $matches[1] ?: '=';
                if ($op !== '==' && $op !== '=' && !empty($stabilityModifier) && self::parseStability($version) === 'stable') {
                    $version .= '-' . $stabilityModifier;
                } elseif ('<' === $op || '>=' === $op) {
                    if (!\preg_match('/-' . self::$modifierRegex . '$/', \strtolower($matches[2]))) {
                        if (\strpos($matches[2], 'dev-') !== 0) {
                            $version .= '-dev';
                        }
                    }
                }
                return array(new Constraint($matches[1] ?: '=', $version));
            } catch (\Exception $e) {
            }
        }
        $message = 'Could not parse version constraint ' . $constraint;
        if (isset($e)) {
            $message .= ': ' . $e->getMessage();
        }
        throw new \UnexpectedValueException($message);
    }
    /**
    @phpstan-param
    */
    private function manipulateVersionString(array $matches, $position, $increment = 0, $pad = '0')
    {
        for ($i = 4; $i > 0; --$i) {
            if ($i > $position) {
                $matches[$i] = $pad;
            } elseif ($i === $position && $increment) {
                $matches[$i] += $increment;
                if ($matches[$i] < 0) {
                    $matches[$i] = $pad;
                    --$position;
                    if ($i === 1) {
                        return null;
                    }
                }
            }
        }
        return $matches[1] . '.' . $matches[2] . '.' . $matches[3] . '.' . $matches[4];
    }
    private function expandStability($stability)
    {
        $stability = \strtolower($stability);
        switch ($stability) {
            case 'a':
                return 'alpha';
            case 'b':
                return 'beta';
            case 'p':
            case 'pl':
                return 'patch';
            case 'rc':
                return 'RC';
            default:
                return $stability;
        }
    }
}
