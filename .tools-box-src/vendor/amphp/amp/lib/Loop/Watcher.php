<?php

namespace _HumbugBoxb47773b41c19\Amp\Loop;

use _HumbugBoxb47773b41c19\Amp\Struct;
/**
@template
@psalm-suppress
*/
class Watcher
{
    use Struct;
    const IO = 0b11;
    const READABLE = 0b1;
    const WRITABLE = 0b10;
    const DEFER = 0b100;
    const TIMER = 0b11000;
    const DELAY = 0b1000;
    const REPEAT = 0b10000;
    const SIGNAL = 0b100000;
    public $type;
    public $enabled = \true;
    public $referenced = \true;
    public $id;
    public $callback;
    public $data;
    /**
    @psalm-var
    */
    public $value;
    public $expiration;
}
