<?php

namespace Infection\Tests\Fixtures\Coverage;

class IgnoreMethodClass
{
    /**
     * @codeCoverageIgnore
     */
    public function getThree(): int
    {
        return 1 + 2;
    }
}
