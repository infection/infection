<?php

namespace IdenticalEqualIntersectionType;

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
doFoo() === doFoo();
doFoo() == doFoo();
