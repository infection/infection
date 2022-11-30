<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Infection\Event\MutationTestingWasFinished;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Filesystem;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Finder;
final class CleanUpAfterMutationTestingFinishedSubscriber implements EventSubscriber
{
    public function __construct(private Filesystem $filesystem, private string $tmpDir)
    {
    }
    public function onMutationTestingWasFinished(MutationTestingWasFinished $event) : void
    {
        $finder = Finder::create()->in($this->tmpDir)->notName('/\\.phpunit\\.result\\.cache\\.(.*)/');
        $this->filesystem->remove($finder);
    }
}
