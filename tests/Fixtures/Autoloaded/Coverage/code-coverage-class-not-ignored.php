<?php

namespace Infection\Tests\Fixtures\Coverage;

class NotIgnoredClass
{
    public function getThree(): int
    {
        return 1 + 2;
    }
}
