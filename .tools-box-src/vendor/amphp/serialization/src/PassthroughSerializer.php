<?php

namespace _HumbugBoxb47773b41c19\Amp\Serialization;

final class PassthroughSerializer implements Serializer
{
    public function serialize($data) : string
    {
        if (!\is_string($data)) {
            throw new SerializationException('Serializer implementation only allows strings');
        }
        return $data;
    }
    public function unserialize(string $data) : string
    {
        return $data;
    }
}
