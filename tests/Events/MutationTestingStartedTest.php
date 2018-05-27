<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Events;

use Infection\Events\MutationTestingStarted;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MutationTestingStartedTest extends TestCase
{
    public function test_it_passes_along_its_mutation_count_without_changing_it()
    {
        $count = 5;
        $event = new MutationTestingStarted($count);

        $this->assertSame($count, $event->getMutationCount());
    }
}
