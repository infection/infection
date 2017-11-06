<?php

namespace SameAbstract;

abstract class SameAbstract
{
    abstract public function foo();
}

class Child extends SameAbstract
{
    public function foo() {}
}