<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Process\Listener;

use Infection\Events\MutationTestingFinished;
use Infection\Process\Listener\CleanUpAfterMutationTestingFinishedSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class CleanUpAfterMutationTestingFinishedSubscriberTest extends TestCase
{
    public function test_it_execute_remove_on_mutation_testing_finished(): void
    {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('remove');

        $subscriber = new CleanUpAfterMutationTestingFinishedSubscriber($filesystem, sys_get_temp_dir());

        $subscriber->onMutationTestingFinished(new MutationTestingFinished());
    }
}
