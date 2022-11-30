<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Process\Runner;

use function array_shift;
use function count;
use Generator;
use function max;
use function microtime;
use function range;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Exception\ProcessTimedOutException;
use function usleep;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class ParallelProcessRunner implements ProcessRunner
{
    private array $runningProcesses = [];
    private array $availableThreadIndexes = [];
    public function __construct(private int $threadCount, private int $poll = 1000)
    {
    }
    public function run(iterable $processes) : void
    {
        $generator = self::toGenerator($processes);
        $bucket = [];
        self::fillBucketOnce($bucket, $generator, 1);
        $threadCount = max(1, $this->threadCount);
        $this->availableThreadIndexes = range(1, $threadCount);
        while ($process = array_shift($bucket)) {
            $threadIndex = array_shift($this->availableThreadIndexes);
            Assert::integer($threadIndex, 'Thread index can not be null.');
            $this->startProcess($process, $threadIndex);
            if (count($this->runningProcesses) >= $threadCount) {
                do {
                    usleep(max(0, $this->poll - self::fillBucketOnce($bucket, $generator, $threadCount)));
                } while (!$this->freeTerminatedProcesses());
            }
            self::fillBucketOnce($bucket, $generator, 1);
        }
        do {
            usleep($this->poll);
            $this->freeTerminatedProcesses();
        } while ($this->runningProcesses);
    }
    private function freeTerminatedProcesses() : bool
    {
        foreach ($this->runningProcesses as $index => $indexedProcessBearer) {
            $processBearer = $indexedProcessBearer->processBearer;
            $process = $processBearer->getProcess();
            try {
                $process->checkTimeout();
            } catch (ProcessTimedOutException) {
                $processBearer->markAsTimedOut();
            }
            if (!$process->isRunning()) {
                $processBearer->terminateProcess();
                $this->availableThreadIndexes[] = $indexedProcessBearer->threadIndex;
                unset($this->runningProcesses[$index]->processBearer);
                unset($this->runningProcesses[$index]);
                return \true;
            }
        }
        return \false;
    }
    private function startProcess(ProcessBearer $processBearer, int $threadIndex) : void
    {
        $processBearer->getProcess()->start(null, ['INFECTION' => '1', 'TEST_TOKEN' => $threadIndex]);
        $this->runningProcesses[] = new IndexedProcessBearer($threadIndex, $processBearer);
    }
    private static function fillBucketOnce(array &$bucket, Generator $input, int $threadCount) : int
    {
        if (count($bucket) >= $threadCount || !$input->valid()) {
            return 0;
        }
        $start = microtime(\true);
        $bucket[] = $input->current();
        $input->next();
        return (int) (microtime(\true) - $start) * 1000000;
    }
    private static function toGenerator(iterable &$input) : Generator
    {
        yield from $input;
    }
}
