<?php

namespace _HumbugBoxb47773b41c19\Amp\Process\Internal;

use _HumbugBoxb47773b41c19\Amp\Deferred;
use _HumbugBoxb47773b41c19\Amp\Process\ProcessInputStream;
use _HumbugBoxb47773b41c19\Amp\Process\ProcessOutputStream;
use _HumbugBoxb47773b41c19\Amp\Struct;
abstract class ProcessHandle
{
    use Struct;
    public $stdin;
    public $stdout;
    public $stderr;
    public $pidDeferred;
    public $status = ProcessStatus::STARTING;
}
