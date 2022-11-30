<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\SebastianBergmann\Diff;

final class Diff
{
    private $from;
    private $to;
    private $chunks;
    public function __construct(string $from, string $to, array $chunks = [])
    {
        $this->from = $from;
        $this->to = $to;
        $this->chunks = $chunks;
    }
    public function getFrom() : string
    {
        return $this->from;
    }
    public function getTo() : string
    {
        return $this->to;
    }
    public function getChunks() : array
    {
        return $this->chunks;
    }
    public function setChunks(array $chunks) : void
    {
        $this->chunks = $chunks;
    }
}
