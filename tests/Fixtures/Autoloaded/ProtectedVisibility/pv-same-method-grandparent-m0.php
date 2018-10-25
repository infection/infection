<?php

namespace ProtectedSameGrandParent;

class SameGrandParent
{
    private function foo()
    {
    }
}
class SameParent extends SameGrandParent
{
}
class Child extends SameParent
{
    protected function foo()
    {
    }
}
