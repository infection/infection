<?php

namespace _HumbugBoxb47773b41c19\Amp\ByteStream;

use _HumbugBoxb47773b41c19\Amp\Promise;
interface InputStream
{
    /**
    @psalm-return
    */
    public function read() : Promise;
}
