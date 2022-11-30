<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Sync;

use _HumbugBoxb47773b41c19\Amp\Parser\Parser;
use _HumbugBoxb47773b41c19\Amp\Serialization\NativeSerializer;
use _HumbugBoxb47773b41c19\Amp\Serialization\Serializer;
use function _HumbugBoxb47773b41c19\Amp\Serialization\encodeUnprintableChars;
final class ChannelParser extends Parser
{
    const HEADER_LENGTH = 5;
    private $serializer;
    public function __construct(callable $callback, ?Serializer $serializer = null)
    {
        $this->serializer = $serializer ?? new NativeSerializer();
        parent::__construct(self::parser($callback, $this->serializer));
    }
    public function encode($data) : string
    {
        $data = $this->serializer->serialize($data);
        return \pack("CL", 0, \strlen($data)) . $data;
    }
    private static function parser(callable $push, Serializer $serializer) : \Generator
    {
        while (\true) {
            $header = (yield self::HEADER_LENGTH);
            $data = \unpack("Cprefix/Llength", $header);
            if ($data["prefix"] !== 0) {
                $data = $header . yield;
                throw new ChannelException("Invalid packet received: " . encodeUnprintableChars($data));
            }
            $data = (yield $data["length"]);
            $push($serializer->unserialize($data));
        }
    }
}
