<?php

namespace _HumbugBoxb47773b41c19\Amp\Process\Internal\Windows;

use _HumbugBoxb47773b41c19\Amp\Struct;
final class PendingSocketClient
{
    use Struct;
    public $readWatcher;
    public $timeoutWatcher;
    public $receivedDataBuffer = '';
    public $pid;
    public $streamId;
}
