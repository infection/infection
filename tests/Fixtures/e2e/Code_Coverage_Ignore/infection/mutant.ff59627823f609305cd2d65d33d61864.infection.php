<?php

namespace CodeCoverageIgnore;

class IgnoreMethod
{
    /**
     * @codeCoverageIgnore
     */
    public function getThree() : int
    {
        return 1 + 2;
    }
    protected function foo() : string
    {
        return 'foo';
    }
}