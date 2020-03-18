<?php

namespace ProtectedSameParent;

class SameParent
{
    protected function foo() {}
}

class Child extends SameParent
{
    protected function foo() {}
}