<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

use _HumbugBoxb47773b41c19\Amp\Coroutine;
use _HumbugBoxb47773b41c19\Amp\Delayed;
use _HumbugBoxb47773b41c19\Amp\Promise;
class FileMutex implements Mutex
{
    public const LATENCY_TIMEOUT = 10;
    private $fileName;
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }
    public function acquire() : Promise
    {
        return new Coroutine($this->doAcquire());
    }
    /**
    @coroutine
    */
    private function doAcquire() : \Generator
    {
        while (($handle = @\fopen($this->fileName, 'x')) === \false) {
            (yield new Delayed(self::LATENCY_TIMEOUT));
        }
        $lock = new Lock(0, function () : void {
            $this->release();
        });
        \fclose($handle);
        return $lock;
    }
    protected function release()
    {
        $success = @\unlink($this->fileName);
        if (!$success) {
            throw new SyncException('Failed to unlock the mutex file.');
        }
    }
}
