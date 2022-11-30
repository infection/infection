<?php

namespace _HumbugBoxb47773b41c19\Amp\Serialization;

final class CompressingSerializer implements Serializer
{
    private const FLAG_COMPRESSED = 1;
    private const COMPRESSION_THRESHOLD = 256;
    private $serializer;
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }
    public function serialize($data) : string
    {
        $serializedData = $this->serializer->serialize($data);
        $flags = 0;
        if (\strlen($serializedData) > self::COMPRESSION_THRESHOLD) {
            $serializedData = @\gzdeflate($serializedData, 1);
            if ($serializedData === \false) {
                $error = \error_get_last();
                throw new SerializationException('Could not compress data: ' . ($error['message'] ?? 'unknown error'));
            }
            $flags |= self::FLAG_COMPRESSED;
        }
        return \chr($flags & 0xff) . $serializedData;
    }
    public function unserialize(string $data)
    {
        $firstByte = \ord($data[0]);
        $data = \substr($data, 1);
        if ($firstByte & self::FLAG_COMPRESSED) {
            $data = @\gzinflate($data);
            if ($data === \false) {
                $error = \error_get_last();
                throw new SerializationException('Could not decompress data: ' . ($error['message'] ?? 'unknown error'));
            }
        }
        return $this->serializer->unserialize($data);
    }
}
