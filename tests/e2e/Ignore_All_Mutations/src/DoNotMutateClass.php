<?php

namespace Ignore_All_Mutations;

/** @infection-ignore-all */
class DoNotMutateClass
{
    public function divide(int $a, int $b): float
    {
        return $a / $b;
    }
}
