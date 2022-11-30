<?php

namespace _HumbugBoxb47773b41c19\parallel;

use Countable;
use parallel\Events\Event;
use parallel\Events\Input;
use Traversable;
final class Events implements Countable, Traversable
{
    public function setInput(Input $input) : void
    {
    }
    public function addChannel(Channel $channel) : void
    {
    }
    public function addFuture(string $name, Future $future) : void
    {
    }
    public function remove(string $target) : void
    {
    }
    public function setBlocking(bool $blocking) : void
    {
    }
    public function setTimeout(int $timeout) : void
    {
    }
    public function poll() : ?Event
    {
    }
    public function count() : int
    {
    }
}
