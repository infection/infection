<?php

namespace InfectionReflectionPartOfSignature;

class Test
{
    public function foo(
        int $param,
        #[CustomAttribute(false)]
        $test = 2.0
    ): bool {
        return count([]) === 1;
    }
}
