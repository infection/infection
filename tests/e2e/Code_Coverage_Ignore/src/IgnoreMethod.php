<?php

namespace CodeCoverageIgnore;

class IgnoreMethod
{
    /**
     * @codeCoverageIgnore
     */
    public function getThree(): int
    {
        return 1 + 2;
    }

    public function foo(): string
    {
        return 'foo';
    }
}
