<?php

namespace FqcnClassAnonymous;

class Ci
{
    public function test()
    {
        return new class() {};
    }
}