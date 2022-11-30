<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\SebastianBergmann\Diff;

final class Line
{
    public const ADDED = 1;
    public const REMOVED = 2;
    public const UNCHANGED = 3;
    private $type;
    private $content;
    public function __construct(int $type = self::UNCHANGED, string $content = '')
    {
        $this->type = $type;
        $this->content = $content;
    }
    public function getContent() : string
    {
        return $this->content;
    }
    public function getType() : int
    {
        return $this->type;
    }
}
