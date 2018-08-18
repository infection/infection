<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Process\Listener;

use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\MutationTestingFinished;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class CleanUpAfterMutationTestingFinishedSubscriber implements EventSubscriberInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $tmpDir;

    public function __construct(Filesystem $filesystem, string $tmpDir)
    {
        $this->filesystem = $filesystem;
        $this->tmpDir = $tmpDir;
    }

    public function getSubscribedEvents(): array
    {
        return [
            MutationTestingFinished::class => [$this, 'onMutationTestingFinished'],
        ];
    }

    public function onMutationTestingFinished(MutationTestingFinished $event): void
    {
        $this->filesystem->remove($this->tmpDir);
    }
}
