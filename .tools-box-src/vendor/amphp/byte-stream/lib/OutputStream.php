<?php

namespace _HumbugBoxb47773b41c19\Amp\ByteStream;

use _HumbugBoxb47773b41c19\Amp\Promise;
interface OutputStream
{
    public function write(string $data) : Promise;
    public function end(string $finalData = "") : Promise;
}
