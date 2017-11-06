<?php

namespace ProtectedSameAbstract;

abstract class SameAbstract
{
    abstract protected function foo();
}

class Child extends SameAbstract
{
    protected function foo() {}
}