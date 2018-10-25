<?php

namespace ProtectedSameParent;

class SameParent
{
    private function foo()
    {
    }
}
class Child extends SameParent
{
    protected function foo()
    {
    }
}
