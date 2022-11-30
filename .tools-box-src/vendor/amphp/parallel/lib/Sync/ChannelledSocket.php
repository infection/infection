<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Sync;

use _HumbugBoxb47773b41c19\Amp\ByteStream\ResourceInputStream;
use _HumbugBoxb47773b41c19\Amp\ByteStream\ResourceOutputStream;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Serialization\Serializer;
final class ChannelledSocket implements Channel
{
    private $channel;
    private $read;
    private $write;
    public function __construct($read, $write, ?Serializer $serializer = null)
    {
        $this->channel = new ChannelledStream($this->read = new ResourceInputStream($read), $this->write = new ResourceOutputStream($write), $serializer);
    }
    public function receive() : Promise
    {
        return $this->channel->receive();
    }
    public function send($data) : Promise
    {
        return $this->channel->send($data);
    }
    public function unreference() : void
    {
        $this->read->unreference();
    }
    public function reference() : void
    {
        $this->read->reference();
    }
    public function close() : void
    {
        $this->read->close();
        $this->write->close();
    }
}
