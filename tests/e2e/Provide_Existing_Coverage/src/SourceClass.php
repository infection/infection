<?php

namespace ProvideExistingCoverage;

class SourceClass
{
    public function add($num1, $num2)
    {
        return $num1 + $num2;
    }

    public function isTrue($value = true)
    {
        if ($value !== false) {
            return true;
        }
        return false;
    }
}
