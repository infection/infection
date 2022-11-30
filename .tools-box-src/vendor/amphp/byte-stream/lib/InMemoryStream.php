<?php

namespace _HumbugBoxb47773b41c19\Amp\ByteStream;

use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Success;
final class InMemoryStream implements InputStream
{
    private $contents;
    public function __construct(string $contents = null)
    {
        $this->contents = $contents;
    }
    public function read() : Promise
    {
        if ($this->contents === null) {
            return new Success();
        }
        $promise = new Success($this->contents);
        $this->contents = null;
        return $promise;
    }
}
