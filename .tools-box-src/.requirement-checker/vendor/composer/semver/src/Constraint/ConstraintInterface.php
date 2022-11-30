<?php

namespace HumbugBox420\Composer\Semver\Constraint;

interface ConstraintInterface
{
    public function matches(ConstraintInterface $provider);
    /**
    @phpstan-param
    */
    public function compile($otherOperator);
    public function getUpperBound();
    public function getLowerBound();
    public function getPrettyString();
    public function setPrettyString($prettyString);
    public function __toString();
}
