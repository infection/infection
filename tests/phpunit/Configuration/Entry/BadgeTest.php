<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration\Entry;

use Infection\Configuration\Entry\Badge;
use PHPUnit\Framework\TestCase;

class BadgeTest extends TestCase
{
    use BadgeAssertions;

    public function test_it_can_be_instantiated(): void
    {
        $badge = new Badge('master');

        $this->assertBadgeStateIs($badge, 'master');
    }
}
