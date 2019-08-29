<?php

namespace Infection\Tests\Fixtures\Coverage;

class DoNotIgnoreMethodClassWithComment
{
    /**
     * @return int
     */
    public function getThree(): int
    {
        return 1 + 2;
    }
}
