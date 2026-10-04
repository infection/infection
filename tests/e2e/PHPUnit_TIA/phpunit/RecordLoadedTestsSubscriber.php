<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\PHPUnit;

use Override;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\TestSuite\Loaded;
use PHPUnit\Event\TestSuite\LoadedSubscriber;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Assert\Assert;
use function array_map;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final readonly class RecordLoadedTestsSubscriber implements LoadedSubscriber
{
    public function __construct(
        private string $filePath,
        private Filesystem $filesystem,
    ) {
        Assert::stringNotEmpty($filePath, 'The loaded test recording path must not be empty.');

        $this->resetRecordingForNewInitialRun();
    }

    #[Override]
    public function notify(Loaded $event): void
    {
        $this->filesystem->dumpFile(
            $this->filePath,
            $this->createRecord($event),
        );
    }

    private function createRecord(Loaded $event): string
    {
        return json_encode(
            array_map(
                static fn (Test $test): string => $test->id(),
                $event->testSuite()->tests()->asArray(),
            ),
            JSON_THROW_ON_ERROR,
        );
    }

    private function resetRecordingForNewInitialRun(): void
    {
        $this->filesystem->dumpFile($this->filePath, '[]');
    }
}
