<?php

namespace HumbugBox420\Composer\Semver\Constraint;

class MatchNoneConstraint implements ConstraintInterface
{
    protected $prettyString;
    public function matches(ConstraintInterface $provider)
    {
        return \false;
    }
    public function compile($otherOperator)
    {
        return 'false';
    }
    public function setPrettyString($prettyString)
    {
        $this->prettyString = $prettyString;
    }
    public function getPrettyString()
    {
        if ($this->prettyString) {
            return $this->prettyString;
        }
        return (string) $this;
    }
    public function __toString()
    {
        return '[]';
    }
    public function getUpperBound()
    {
        return new Bound('0.0.0.0-dev', \false);
    }
    public function getLowerBound()
    {
        return new Bound('0.0.0.0-dev', \false);
    }
}
