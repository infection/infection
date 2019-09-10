<?php

namespace ParamCoverage;

abstract class AbstractSearcher
{
    abstract public function search($value, bool $strict = false);
}
