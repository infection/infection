<?php

namespace InfectionReflectionClosure;

class ClassWithAnonymousFunction
{
    public function bar()
    {
        return function() {
            return 1;
        };
    }

}
