<?php

namespace InfectionReflectionAnonymousClass;

class A{}

class Bug2
{
    public function createAnonymousClass()
    {
        new class extends A
        {
            public function foo()
            {
            }
        };
    }
}


