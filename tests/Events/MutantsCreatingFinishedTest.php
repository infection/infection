<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Events;

use Infection\Events\MutantsCreatingFinished;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MutantsCreatingFinishedTest extends TestCase
{
    /**
     * This class is only used to fire events, and the only functionality it needs is being instantiated
     */
    public function test_it_can_be_initialzed(): void
    {
        $class = new MutantsCreatingFinished();
        $this->assertInstanceOf(MutantsCreatingFinished::class, $class);
    }
}
