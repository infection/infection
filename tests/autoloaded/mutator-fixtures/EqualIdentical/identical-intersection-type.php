<?php

namespace EqualIdenticalIntersectionType;

interface A
{
}
interface B
{
}
class C implements A, B
{
}
function doFoo(): A&B
{
    return new C();
}

class Demo {
    function compareFoos() {
        doFoo() === doFoo();
        doFoo() == doFoo();
    }
}
