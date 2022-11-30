<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\SebastianBergmann\Diff;

final class Chunk
{
    private $start;
    private $startRange;
    private $end;
    private $endRange;
    private $lines;
    public function __construct(int $start = 0, int $startRange = 1, int $end = 0, int $endRange = 1, array $lines = [])
    {
        $this->start = $start;
        $this->startRange = $startRange;
        $this->end = $end;
        $this->endRange = $endRange;
        $this->lines = $lines;
    }
    public function getStart() : int
    {
        return $this->start;
    }
    public function getStartRange() : int
    {
        return $this->startRange;
    }
    public function getEnd() : int
    {
        return $this->end;
    }
    public function getEndRange() : int
    {
        return $this->endRange;
    }
    public function getLines() : array
    {
        return $this->lines;
    }
    public function setLines(array $lines) : void
    {
        foreach ($lines as $line) {
            if (!$line instanceof Line) {
                throw new InvalidArgumentException();
            }
        }
        $this->lines = $lines;
    }
}
