<?php

namespace PublicVisibility_AnyInterface;

interface FirstInterface
{
}

interface SecondInterface
{
    public function foo();
}

interface ThirdInterface
{
}

class Child implements FirstInterface, SecondInterface, ThirdInterface
{
    public function foo() {}
}