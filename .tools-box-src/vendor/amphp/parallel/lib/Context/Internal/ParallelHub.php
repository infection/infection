<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Context\Internal;

use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ChannelledSocket;
use parallel\Events;
use parallel\Future;
class ParallelHub extends ProcessHub
{
    const EXIT_CHECK_FREQUENCY = 250;
    private $channels;
    private $watcher;
    private $events;
    public function __construct()
    {
        parent::__construct();
        $events = $this->events = new Events();
        $this->events->setBlocking(\false);
        $channels =& $this->channels;
        $this->watcher = Loop::repeat(self::EXIT_CHECK_FREQUENCY, static function () use(&$channels, $events) : void {
            while ($event = $events->poll()) {
                $id = (int) $event->source;
                \assert(isset($channels[$id]), 'Channel for context ID not found');
                $channel = $channels[$id];
                unset($channels[$id]);
                $channel->close();
            }
        });
        Loop::disable($this->watcher);
        Loop::unreference($this->watcher);
    }
    public function add(int $id, ChannelledSocket $channel, Future $future) : void
    {
        $this->channels[$id] = $channel;
        $this->events->addFuture((string) $id, $future);
        Loop::enable($this->watcher);
    }
    public function remove(int $id) : void
    {
        if (!isset($this->channels[$id])) {
            return;
        }
        unset($this->channels[$id]);
        $this->events->remove((string) $id);
        if (empty($this->channels)) {
            Loop::disable($this->watcher);
        }
    }
}
