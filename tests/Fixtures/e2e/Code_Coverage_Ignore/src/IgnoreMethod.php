<?php

namespace CodeCoverageIgnore;

class IgnoreMethod
{
    /**
     * @codeCoverageIgnore
     */
    public function getThree(): string
    {
        return 1 + 2;
    }

    public function foo(): string
    {
        return 'foo';
    }
}
