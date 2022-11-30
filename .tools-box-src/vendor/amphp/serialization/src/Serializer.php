<?php

namespace _HumbugBoxb47773b41c19\Amp\Serialization;

interface Serializer
{
    public function serialize($data) : string;
    public function unserialize(string $data);
}
