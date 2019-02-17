<?php

namespace InfectionReflectionAnonymousClass;

class Bug
{
    public function createAnonymousClass()
    {
        new class
        {
            public function foo()
            {
            }
        };
    }
}
