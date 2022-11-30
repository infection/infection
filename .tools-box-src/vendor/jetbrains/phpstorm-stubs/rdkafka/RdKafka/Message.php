<?php

namespace _HumbugBoxb47773b41c19\RdKafka;

class Message
{
    public $err;
    public $topic_name;
    public $timestamp;
    public $partition;
    public $payload;
    public $len;
    public $key;
    public $offset;
    public $headers;
    public $opaque;
    public function errstr()
    {
    }
}
