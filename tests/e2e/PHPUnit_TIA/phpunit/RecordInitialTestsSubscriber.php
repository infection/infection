<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\PHPUnit;

use function json_encode;
use const JSON_THROW_ON_ERROR;
use Override;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Assert\Assert;

final readonly class RecordInitialTestsSubscriber implements PreparedSubscriber
{
    public function __construct(
        private string $filePath,
        private Filesystem $filesystem,
    )
    {
        Assert::stringNotEmpty($filePath, 'The initial test recording path must not be empty.');

        $this->resetRecordingForNewInitialRun();
    }

    #[Override]
    public function notify(Prepared $event): void
    {
        $this->filesystem->appendToFile(
            $this->filePath,
            $this->createRecord($event)."\n",
            lock: true,
        );
    }

    private function createRecord(Prepared $event): string
    {
        return json_encode(
            $event->test()->id(),
            JSON_THROW_ON_ERROR,
        );
    }

    private function resetRecordingForNewInitialRun(): void
    {
        $this->filesystem->dumpFile($this->filePath, '');
    }
}
