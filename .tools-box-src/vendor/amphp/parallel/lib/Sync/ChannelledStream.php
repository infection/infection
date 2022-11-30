<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Sync;

use _HumbugBoxb47773b41c19\Amp\ByteStream\InputStream;
use _HumbugBoxb47773b41c19\Amp\ByteStream\OutputStream;
use _HumbugBoxb47773b41c19\Amp\ByteStream\StreamException;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Serialization\Serializer;
use function _HumbugBoxb47773b41c19\Amp\call;
final class ChannelledStream implements Channel
{
    private $read;
    private $write;
    private $received;
    private $parser;
    public function __construct(InputStream $read, OutputStream $write, ?Serializer $serializer = null)
    {
        $this->read = $read;
        $this->write = $write;
        $this->received = new \SplQueue();
        $this->parser = new ChannelParser([$this->received, 'push'], $serializer);
    }
    public function send($data) : Promise
    {
        return call(function () use($data) : \Generator {
            try {
                return (yield $this->write->write($this->parser->encode($data)));
            } catch (StreamException $exception) {
                throw new ChannelException("Sending on the channel failed. Did the context die?", 0, $exception);
            }
        });
    }
    public function receive() : Promise
    {
        return call(function () : \Generator {
            while ($this->received->isEmpty()) {
                try {
                    $chunk = (yield $this->read->read());
                } catch (StreamException $exception) {
                    throw new ChannelException("Reading from the channel failed. Did the context die?", 0, $exception);
                }
                if ($chunk === null) {
                    throw new ChannelException("The channel closed unexpectedly. Did the context die?");
                }
                $this->parser->push($chunk);
            }
            return $this->received->shift();
        });
    }
}
