<?php

namespace _HumbugBoxb47773b41c19\Amp\Internal;

use _HumbugBoxb47773b41c19\Amp\Iterator;
use _HumbugBoxb47773b41c19\Amp\Promise;
/**
@template-covariant
@template-implements
*/
final class PrivateIterator implements Iterator
{
    private $iterator;
    /**
    @psalm-param
    */
    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }
    public function advance() : Promise
    {
        return $this->iterator->advance();
    }
    /**
    @psalm-return
    */
    public function getCurrent()
    {
        return $this->iterator->getCurrent();
    }
}
