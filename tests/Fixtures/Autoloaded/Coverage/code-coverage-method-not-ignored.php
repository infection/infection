<?php

namespace Infection\Tests\Fixtures\Coverage;

class DoNotIgnoreMethodClass
{
    public function getThree(): int
    {
        return 1 + 2;
    }
}
