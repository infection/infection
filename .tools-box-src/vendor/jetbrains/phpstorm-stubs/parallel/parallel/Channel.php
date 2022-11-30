<?php

namespace _HumbugBoxb47773b41c19\parallel;

final class Channel
{
    public const Infinite = -1;
    public function __construct(?int $capacity = null)
    {
    }
    public static function make(string $name, ?int $capacity = null) : Channel
    {
    }
    public static function open(string $name) : Channel
    {
    }
    public function send($value) : void
    {
    }
    public function recv()
    {
    }
    public function close() : void
    {
    }
    public function __toString() : string
    {
    }
}
