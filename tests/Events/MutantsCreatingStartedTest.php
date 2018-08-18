<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Events;

use Infection\Events\MutantsCreatingStarted;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MutantsCreatingStartedTest extends TestCase
{
    public function test_it_passes_along_its_mutation_count_without_changing_it(): void
    {
        $count = 5;
        $event = new MutantsCreatingStarted($count);

        $this->assertSame($count, $event->getMutantCount());
    }
}
