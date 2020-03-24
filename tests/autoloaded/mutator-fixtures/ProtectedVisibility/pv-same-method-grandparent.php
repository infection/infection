<?php

namespace ProtectedSameGrandParent;

class SameGrandParent
{
    protected function foo() {}
}

class SameParent extends SameGrandParent
{

}

class Child extends SameParent
{
    protected function foo() {}
}