<?php

namespace Infection\Tests\Fixtures\Coverage;

/**
 * This class does have a comment, but is not ignored
 */
class NotIgnoredClassWithComment
{
    public function getThree(): int
    {
        return 1 + 2;
    }
}
