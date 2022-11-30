<?php

namespace _HumbugBoxb47773b41c19\Composer\Semver;

use _HumbugBoxb47773b41c19\Composer\Semver\Constraint\Constraint;
use _HumbugBoxb47773b41c19\Composer\Semver\Constraint\ConstraintInterface;
use _HumbugBoxb47773b41c19\Composer\Semver\Constraint\MatchAllConstraint;
use _HumbugBoxb47773b41c19\Composer\Semver\Constraint\MatchNoneConstraint;
use _HumbugBoxb47773b41c19\Composer\Semver\Constraint\MultiConstraint;
class Intervals
{
    /**
    @phpstan-var
    */
    private static $intervalsCache = array();
    /**
    @phpstan-var
    */
    private static $opSortOrder = array('>=' => -3, '<' => -2, '>' => 2, '<=' => 3);
    public static function clear()
    {
        self::$intervalsCache = array();
    }
    public static function isSubsetOf(ConstraintInterface $candidate, ConstraintInterface $constraint)
    {
        if ($constraint instanceof MatchAllConstraint) {
            return \true;
        }
        if ($candidate instanceof MatchNoneConstraint || $constraint instanceof MatchNoneConstraint) {
            return \false;
        }
        $intersectionIntervals = self::get(new MultiConstraint(array($candidate, $constraint), \true));
        $candidateIntervals = self::get($candidate);
        if (\count($intersectionIntervals['numeric']) !== \count($candidateIntervals['numeric'])) {
            return \false;
        }
        foreach ($intersectionIntervals['numeric'] as $index => $interval) {
            if (!isset($candidateIntervals['numeric'][$index])) {
                return \false;
            }
            if ((string) $candidateIntervals['numeric'][$index]->getStart() !== (string) $interval->getStart()) {
                return \false;
            }
            if ((string) $candidateIntervals['numeric'][$index]->getEnd() !== (string) $interval->getEnd()) {
                return \false;
            }
        }
        if ($intersectionIntervals['branches']['exclude'] !== $candidateIntervals['branches']['exclude']) {
            return \false;
        }
        if (\count($intersectionIntervals['branches']['names']) !== \count($candidateIntervals['branches']['names'])) {
            return \false;
        }
        foreach ($intersectionIntervals['branches']['names'] as $index => $name) {
            if ($name !== $candidateIntervals['branches']['names'][$index]) {
                return \false;
            }
        }
        return \true;
    }
    public static function haveIntersections(ConstraintInterface $a, ConstraintInterface $b)
    {
        if ($a instanceof MatchAllConstraint || $b instanceof MatchAllConstraint) {
            return \true;
        }
        if ($a instanceof MatchNoneConstraint || $b instanceof MatchNoneConstraint) {
            return \false;
        }
        $intersectionIntervals = self::generateIntervals(new MultiConstraint(array($a, $b), \true), \true);
        return \count($intersectionIntervals['numeric']) > 0 || $intersectionIntervals['branches']['exclude'] || \count($intersectionIntervals['branches']['names']) > 0;
    }
    public static function compactConstraint(ConstraintInterface $constraint)
    {
        if (!$constraint instanceof MultiConstraint) {
            return $constraint;
        }
        $intervals = self::generateIntervals($constraint);
        $constraints = array();
        $hasNumericMatchAll = \false;
        if (\count($intervals['numeric']) === 1 && (string) $intervals['numeric'][0]->getStart() === (string) Interval::fromZero() && (string) $intervals['numeric'][0]->getEnd() === (string) Interval::untilPositiveInfinity()) {
            $constraints[] = $intervals['numeric'][0]->getStart();
            $hasNumericMatchAll = \true;
        } else {
            $unEqualConstraints = array();
            for ($i = 0, $count = \count($intervals['numeric']); $i < $count; $i++) {
                $interval = $intervals['numeric'][$i];
                if ($interval->getEnd()->getOperator() === '<' && $i + 1 < $count) {
                    $nextInterval = $intervals['numeric'][$i + 1];
                    if ($interval->getEnd()->getVersion() === $nextInterval->getStart()->getVersion() && $nextInterval->getStart()->getOperator() === '>') {
                        if (\count($unEqualConstraints) === 0 && (string) $interval->getStart() !== (string) Interval::fromZero()) {
                            $unEqualConstraints[] = $interval->getStart();
                        }
                        $unEqualConstraints[] = new Constraint('!=', $interval->getEnd()->getVersion());
                        continue;
                    }
                }
                if (\count($unEqualConstraints) > 0) {
                    if ((string) $interval->getEnd() !== (string) Interval::untilPositiveInfinity()) {
                        $unEqualConstraints[] = $interval->getEnd();
                    }
                    if (\count($unEqualConstraints) > 1) {
                        $constraints[] = new MultiConstraint($unEqualConstraints, \true);
                    } else {
                        $constraints[] = $unEqualConstraints[0];
                    }
                    $unEqualConstraints = array();
                    continue;
                }
                if ($interval->getStart()->getVersion() === $interval->getEnd()->getVersion() && $interval->getStart()->getOperator() === '>=' && $interval->getEnd()->getOperator() === '<=') {
                    $constraints[] = new Constraint('==', $interval->getStart()->getVersion());
                    continue;
                }
                if ((string) $interval->getStart() === (string) Interval::fromZero()) {
                    $constraints[] = $interval->getEnd();
                } elseif ((string) $interval->getEnd() === (string) Interval::untilPositiveInfinity()) {
                    $constraints[] = $interval->getStart();
                } else {
                    $constraints[] = new MultiConstraint(array($interval->getStart(), $interval->getEnd()), \true);
                }
            }
        }
        $devConstraints = array();
        if (0 === \count($intervals['branches']['names'])) {
            if ($intervals['branches']['exclude']) {
                if ($hasNumericMatchAll) {
                    return new MatchAllConstraint();
                }
            }
        } else {
            foreach ($intervals['branches']['names'] as $branchName) {
                if ($intervals['branches']['exclude']) {
                    $devConstraints[] = new Constraint('!=', $branchName);
                } else {
                    $devConstraints[] = new Constraint('==', $branchName);
                }
            }
            if ($intervals['branches']['exclude']) {
                if (\count($constraints) > 1) {
                    return new MultiConstraint(\array_merge(array(new MultiConstraint($constraints, \false)), $devConstraints), \true);
                }
                if (\count($constraints) === 1 && (string) $constraints[0] === (string) Interval::fromZero()) {
                    if (\count($devConstraints) > 1) {
                        return new MultiConstraint($devConstraints, \true);
                    }
                    return $devConstraints[0];
                }
                return new MultiConstraint(\array_merge($constraints, $devConstraints), \true);
            }
            $constraints = \array_merge($constraints, $devConstraints);
        }
        if (\count($constraints) > 1) {
            return new MultiConstraint($constraints, \false);
        }
        if (\count($constraints) === 1) {
            return $constraints[0];
        }
        return new MatchNoneConstraint();
    }
    /**
    @phpstan-return
    */
    public static function get(ConstraintInterface $constraint)
    {
        $key = (string) $constraint;
        if (!isset(self::$intervalsCache[$key])) {
            self::$intervalsCache[$key] = self::generateIntervals($constraint);
        }
        return self::$intervalsCache[$key];
    }
    /**
    @phpstan-return
    */
    private static function generateIntervals(ConstraintInterface $constraint, $stopOnFirstValidInterval = \false)
    {
        if ($constraint instanceof MatchAllConstraint) {
            return array('numeric' => array(new Interval(Interval::fromZero(), Interval::untilPositiveInfinity())), 'branches' => Interval::anyDev());
        }
        if ($constraint instanceof MatchNoneConstraint) {
            return array('numeric' => array(), 'branches' => array('names' => array(), 'exclude' => \false));
        }
        if ($constraint instanceof Constraint) {
            return self::generateSingleConstraintIntervals($constraint);
        }
        if (!$constraint instanceof MultiConstraint) {
            throw new \UnexpectedValueException('The constraint passed in should be an MatchAllConstraint, Constraint or MultiConstraint instance, got ' . \get_class($constraint) . '.');
        }
        $constraints = $constraint->getConstraints();
        $numericGroups = array();
        $constraintBranches = array();
        foreach ($constraints as $c) {
            $res = self::get($c);
            $numericGroups[] = $res['numeric'];
            $constraintBranches[] = $res['branches'];
        }
        if ($constraint->isDisjunctive()) {
            $branches = Interval::noDev();
            foreach ($constraintBranches as $b) {
                if ($b['exclude']) {
                    if ($branches['exclude']) {
                        $branches['names'] = \array_intersect($branches['names'], $b['names']);
                    } else {
                        $branches['exclude'] = \true;
                        $branches['names'] = \array_diff($b['names'], $branches['names']);
                    }
                } else {
                    if ($branches['exclude']) {
                        $branches['names'] = \array_diff($branches['names'], $b['names']);
                    } else {
                        $branches['names'] = \array_merge($branches['names'], $b['names']);
                    }
                }
            }
        } else {
            $branches = Interval::anyDev();
            foreach ($constraintBranches as $b) {
                if ($b['exclude']) {
                    if ($branches['exclude']) {
                        $branches['names'] = \array_merge($branches['names'], $b['names']);
                    } else {
                        $branches['names'] = \array_diff($branches['names'], $b['names']);
                    }
                } else {
                    if ($branches['exclude']) {
                        $branches['names'] = \array_diff($b['names'], $branches['names']);
                        $branches['exclude'] = \false;
                    } else {
                        $branches['names'] = \array_intersect($branches['names'], $b['names']);
                    }
                }
            }
        }
        $branches['names'] = \array_unique($branches['names']);
        if (\count($numericGroups) === 1) {
            return array('numeric' => $numericGroups[0], 'branches' => $branches);
        }
        $borders = array();
        foreach ($numericGroups as $group) {
            foreach ($group as $interval) {
                $borders[] = array('version' => $interval->getStart()->getVersion(), 'operator' => $interval->getStart()->getOperator(), 'side' => 'start');
                $borders[] = array('version' => $interval->getEnd()->getVersion(), 'operator' => $interval->getEnd()->getOperator(), 'side' => 'end');
            }
        }
        $opSortOrder = self::$opSortOrder;
        \usort($borders, function ($a, $b) use($opSortOrder) {
            $order = \version_compare($a['version'], $b['version']);
            if ($order === 0) {
                return $opSortOrder[$a['operator']] - $opSortOrder[$b['operator']];
            }
            return $order;
        });
        $activeIntervals = 0;
        $intervals = array();
        $index = 0;
        $activationThreshold = $constraint->isConjunctive() ? \count($numericGroups) : 1;
        $start = null;
        foreach ($borders as $border) {
            if ($border['side'] === 'start') {
                $activeIntervals++;
            } else {
                $activeIntervals--;
            }
            if (!$start && $activeIntervals >= $activationThreshold) {
                $start = new Constraint($border['operator'], $border['version']);
            } elseif ($start && $activeIntervals < $activationThreshold) {
                if (\version_compare($start->getVersion(), $border['version'], '=') && ($start->getOperator() === '>' && $border['operator'] === '<=' || $start->getOperator() === '>=' && $border['operator'] === '<')) {
                    unset($intervals[$index]);
                } else {
                    $intervals[$index] = new Interval($start, new Constraint($border['operator'], $border['version']));
                    $index++;
                    if ($stopOnFirstValidInterval) {
                        break;
                    }
                }
                $start = null;
            }
        }
        return array('numeric' => $intervals, 'branches' => $branches);
    }
    /**
    @phpstan-return
    */
    private static function generateSingleConstraintIntervals(Constraint $constraint)
    {
        $op = $constraint->getOperator();
        if (\strpos($constraint->getVersion(), 'dev-') === 0) {
            $intervals = array();
            $branches = array('names' => array(), 'exclude' => \false);
            if ($op === '!=') {
                $intervals[] = new Interval(Interval::fromZero(), Interval::untilPositiveInfinity());
                $branches = array('names' => array($constraint->getVersion()), 'exclude' => \true);
            } elseif ($op === '==') {
                $branches['names'][] = $constraint->getVersion();
            }
            return array('numeric' => $intervals, 'branches' => $branches);
        }
        if ($op[0] === '>') {
            return array('numeric' => array(new Interval($constraint, Interval::untilPositiveInfinity())), 'branches' => Interval::noDev());
        }
        if ($op[0] === '<') {
            return array('numeric' => array(new Interval(Interval::fromZero(), $constraint)), 'branches' => Interval::noDev());
        }
        if ($op === '!=') {
            return array('numeric' => array(new Interval(Interval::fromZero(), new Constraint('<', $constraint->getVersion())), new Interval(new Constraint('>', $constraint->getVersion()), Interval::untilPositiveInfinity())), 'branches' => Interval::anyDev());
        }
        return array('numeric' => array(new Interval(new Constraint('>=', $constraint->getVersion()), new Constraint('<=', $constraint->getVersion()))), 'branches' => Interval::noDev());
    }
}
