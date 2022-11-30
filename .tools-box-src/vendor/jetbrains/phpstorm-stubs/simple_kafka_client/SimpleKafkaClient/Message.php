<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\SimpleKafkaClient;

class Message
{
    public int $err;
    public string $topic_name;
    public int $timestamp;
    public int $partition;
    public int $payload;
    public int $len;
    public string $key;
    public int $offset;
    public array $headers;
    public function getErrorString() : string
    {
    }
}
